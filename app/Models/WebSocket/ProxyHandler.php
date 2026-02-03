<?php

/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types = 1);

namespace App\Models\WebSocket;

use App\ApiModule\Version0\Models\BearerAuthenticator;
use App\Models\Database\Entities\User;
use App\Models\WebSocket\Enums\ProxyAuthError;
use App\Models\WebSocket\Messages\ProxyAuthFailed;
use App\Models\WebSocket\Messages\ProxySessionExpired;
use Contributte\Monolog\LoggerManager;
use DateTimeImmutable;
use GuzzleHttp\Psr7\Request;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Token\RegisteredClaims;
use Monolog\Level;
use Psr\Log\LoggerInterface;
use Ratchet\Client\Connector;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\RFC6455\Messaging\Frame;
use Ratchet\WebSocket\WsConnection;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Socket\Connector as SocketConnector;
use React\Socket\SecureConnector;
use Throwable;

/**
 * Proxy handler implementation using Ratchet WebSocket libraries
 */
class ProxyHandler implements MessageComponentInterface {

	/**
	 * @var LoggerInterface Logger
	 */
	private readonly LoggerInterface $logger;

	/**
	 * @var LoopInterface Event loop
	 */
	private readonly LoopInterface $loop;

	/**
	 * @var Connector Ratchet connector
	 */
	private readonly Connector $connector;

	/**
	 * @var array<int, ProxySession> Session map
	 */
	private array $sessionMap;

	/**
	 * @var array<int, DateTimeImmutable> Client connection expiration map
	 */
	private array $expMap;

	private Parser $jwtParser;

	/**
	 * Constructs proxy handler object
	 * @param string $upstreamUrl Upstream URL
	 * @param string $upstreamToken Upstream API token
	 * @param BearerAuthenticator $authenticator Authenticator
	 * @param LoggerManager $loggerManager Logger manager
	 */
	public function __construct(
		private readonly string $upstreamUrl,
		private readonly string $upstreamToken,
		private readonly BearerAuthenticator $authenticator,
		LoggerManager $loggerManager,
	) {
		$this->logger = $loggerManager->get('proxy');
		$this->loop = Loop::get();
		$this->connector = $this->createConnector();
		$this->jwtParser = new Parser(new JoseEncoder());
		$this->logger->info('Starting proxy server.');
	}

	/**
	 * Incoming client connection handler.
	 *
	 * Clients are expected to connect with a JWT for authentication as query parameter.
	 * If a token is not attached, the connection is closed with policy error (code 1008).
	 * If the token is present, it is validated and checked for existence of user the token belongs to.
	 * Invalid token also results in connection being closed with policy error (code 1008).
	 *
	 * Valid token is required to continue the proxy chain and for establishing a connection with upstream.
	 * A new session object is created and stored in session map for mapping client
	 * connections to their respective upstream connection counterparts.
	 *
	 * TODO: Store some kind of user identifier / information (probably role) to allow for checking
	 * permission scopes for client messages.
	 *
	 * @param ConnectionInterface $conn Client connection
	 */
	public function onOpen(ConnectionInterface $conn): void {
		assert($conn instanceof WsConnection);

		// @phpstan-ignore property.notFound
		$clientId = $conn->resourceId;
		// @phpstan-ignore property.notFound
		$clientAddr = $conn->remoteAddress;

		$this->logMessage(
			Level::Info,
			'Client connected to proxy server.',
			[
				'addr' => $clientAddr,
				'id' => strval($clientId),
			],
		);

		// @phpstan-ignore property.notFound
		$request = $conn->httpRequest;

		assert($request instanceof Request);

		$queryParams = [];
		parse_str($request->getUri()->getQuery(), $queryParams);

		// check for token
		if (!array_key_exists('token', $queryParams)) {
			$this->logMessage(
				Level::Error,
				'Token missing, closing connection.',
				[
					'addr' => $clientAddr,
					'id' => strval($clientId),
				],
			);
			$conn->send((new ProxyAuthFailed(ProxyAuthError::MISSING_TOKEN))->toJsonString());
			// maybe come up with different code specific for this case, something in the 4000s
			$conn->close(Frame::CLOSE_POLICY);
			return;
		}

		try {
			$user = $this->authenticator->authenticateUser($queryParams['token']);
		} catch (Throwable) {
			$this->logMessage(
				Level::Error,
				'Invalid token format, closing connection.',
				[
					'addr' => $clientAddr,
					'id' => strval($clientId),
				],
			);
			$conn->send((new ProxyAuthFailed(ProxyAuthError::INVALID_TOKEN))->toJsonString());
			// maybe come up with different code specific for this case, something in the 4000s
			$conn->close(Frame::CLOSE_POLICY);
			return;
		}
		if (!($user instanceof User)) {
			$this->logMessage(
				Level::Error,
				'Invalid token, closing connection.',
				[
					'addr' => $clientAddr,
					'id' => strval($clientId),
				],
			);
			$conn->send((new ProxyAuthFailed(ProxyAuthError::INVALID_TOKEN))->toJsonString());
			// maybe come up with different code specific for this case, something in the 4000s
			$conn->close(Frame::CLOSE_POLICY);
			return;
		}

		// get expiration
		$token = $this->jwtParser->parse($queryParams['token']);
		assert($token instanceof Plain);
		$expiration = $token->claims()->get(RegisteredClaims::EXPIRATION_TIME);

		$session = new ProxySession(
			$conn,
			$this->upstreamUrl,
			$this->upstreamToken,
			$this->connector,
			$this->logger,
		);
		$session->registerSessionCloseCallback(
			function (int $clientId): void {
				if (isset($this->sessionMap[$clientId])) {
					unset($this->sessionMap[$clientId]);
				}
				if (isset($this->expMap[$clientId])) {
					unset($this->expMap[$clientId]);
				}
			}
		);
		$this->sessionMap[$clientId] = $session;
		$this->expMap[$clientId] = $expiration;
	}

	/**
	 * Incoming client message handler.
	 *
	 * The message from client is passed to session object holding connection to upstream.
	 * Client connection resource ID is used for finding the correct session in session map.
	 *
	 * TODO: Check if client has permission to perform the API request,
	 * this feature shall be implemented when API scopes are defined and finished.
	 *
	 * @param ConnectionInterface $conn Client connection
	 * @param string $msg Incoming message
	 */
	// phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
	public function onMessage(ConnectionInterface $conn, $msg): void {
		assert($conn instanceof WsConnection);
		// @phpstan-ignore property.notFound
		$clientId = $conn->resourceId;
		// @phpstan-ignore property.notFound
		$clientAddr = $conn->remoteAddress;

		$this->logMessage(
			Level::Debug,
			'Incoming message from client: {message}',
			[
				'addr' => $clientAddr,
				'id' => strval($clientId),
				'message' => $msg,
			],
		);

		if (isset($this->expMap[$clientId])) {
			if (new DateTimeImmutable() >= $this->expMap[$clientId]) {
				$this->logMessage(
					Level::Info,
					'Client session has expired, closing connection.',
					[
						'addr' => $clientAddr,
						'id' => strval($clientId),
					],
				);

				$conn->send((new ProxySessionExpired())->toJsonString());
				$conn->close(Frame::CLOSE_POLICY);
				return;
			}
		}

		if (isset($this->sessionMap[$clientId])) {
			$this->sessionMap[$clientId]->handleClientMessage($msg);
		}
	}

	/**
	 * Client connection close handler.
	 *
	 * Upstream connections only exist for active client connections,
	 * when a client disconnects, the session mapping object is cleaned up.
	 *
	 * @param ConnectionInterface $conn Client connection
	 */
	public function onClose(ConnectionInterface $conn): void {
		// @phpstan-ignore property.notFound
		$clientAddr = $conn->remoteAddress;
		// @phpstan-ignore property.notFound
		$clientId = $conn->resourceId;

		$this->logMessage(
			Level::Info,
			'Client disconnected from proxy server.',
			[
				'addr' => $clientAddr,
				'id' => strval($clientId),
			]
		);

		$this->cleanupUpstreamConnection($clientId, $clientAddr);
	}

	/**
	 * Client connection error handler.
	 *
	 * When an error occurs during the client connection lifetime,
	 * it is effectively unusable and will be closed.
	 * The session mapping object is cleaned up as a result.
	 *
	 * @param ConnectionInterface $conn Client connection
	 * @param Throwable $e Exception containing error
	 */
	public function onError(ConnectionInterface $conn, Throwable $e): void {
		// @phpstan-ignore property.notFound
		$clientAddr = $conn->remoteAddress;
		// @phpstan-ignore property.notFound
		$clientId = $conn->resourceId;

		$this->logMessage(
			Level::Error,
			'Client connection to proxy server lost: {reason}({code}).',
			[
				'addr' => $clientAddr,
				'id' => strval($clientId),
				'reason' => $e->getMessage(),
				'code' => strval($e->getCode()),
			],
		);

		// cleanup session
		$this->cleanupUpstreamConnection($clientId, $clientAddr);
	}

	/**
	 * Creates a new connector for establishing connections with upstream.
	 *
	 * The connector is not used here, but a proxy session when a new client connects.
	 *
	 * @return Connector Connector
	 */
	private function createConnector(): Connector {
		$tls = substr($this->upstreamUrl, 0, 3) === 'wss';
		// TCP socket connector
		$tcpConnector = new SocketConnector(
			// TODO: maybe redo this to use available DNS
			[
				'dns' => '8.8.8.8',
				'timeout' => 10,
			],
			$this->loop,
		);
		// TLS connector
		if ($tls) {
			$tlsConnector = new SecureConnector(
				$tcpConnector,
				$this->loop,
				[
					'verify_peer' => true,
					'verify_peer_name' => true,
					'allow_self_signed' => true,
				],
			);
			return new Connector($this->loop, $tlsConnector);
		}
		return new Connector($this->loop, $tcpConnector);
	}

	/**
	 * Cleans up upstream connection maintained by a proxy session.
	 *
	 * This method is called when client connection is closed, or lost due to an error.
	 * If the proxy session is still available in map, the upstream is closed and session
	 * is removed, ending its lifetime.
	 *
	 * @param int $clientId Client ID
	 * @param string $remoteAddr Address of client
	 * @param int $code Close code
	 * @param string $reason Reason
	 */
	private function cleanupUpstreamConnection(
		int $clientId,
		string $remoteAddr,
		int $code = 1000,
		string $reason = 'Connection closed gracefully'
	): void {
		if (isset($this->sessionMap[$clientId])) {
			$this->sessionMap[$clientId]->closeSession($code, $reason);
			unset($this->sessionMap[$clientId]);
			$this->logMessage(
				Level::Info,
				'Unregistered upstream session from upstream map.',
				[
					'addr' => $remoteAddr,
					'id' => strval($clientId),
				],
			);
		}
	}

	/**
	 * Formats a log message, prefixing it with client ID and address.
	 *
	 * @param Level $level Log severity
	 * @param string $record Record to log
	 * @param array<mixed> $context Context containing parameters
	 */
	private function logMessage(Level $level, string $record, array $context): void {
		$this->logger->log(
			$level,
			'[{id}|{addr}] ' . $record,
			$context,
		);
	}

}
