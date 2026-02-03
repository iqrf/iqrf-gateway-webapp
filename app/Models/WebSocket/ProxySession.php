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

use App\Models\WebSocket\Messages\UpstreamAuthFailed;
use App\Models\WebSocket\Messages\UpstreamDisconnected;
use App\Models\WebSocket\Messages\UpstreamReady;
use App\Models\WebSocket\Messages\UpstreamReconnecting;
use App\Models\WebSocket\Messages\UpstreamRequestFailed;
use App\Models\WebSocket\Messages\UpstreamRequestInvalid;
use App\Models\WebSocket\Messages\UpstreamResponse;
use App\Models\WebSocket\Utils\ProxyMessageValidator;
use Closure;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Psr\Log\LoggerInterface;
use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\EventLoop\Loop;
use React\EventLoop\TimerInterface;
use Throwable;

/**
 * Proxy session class.
 *
 * The session stores pairing of client and upstream connections.
 * Upstream connection handlers are also defined here.
 */
class ProxySession {

	/**
	 * Maximum reconnect backoff delay
	 */
	private const MAX_RECONNECT_DELAY = 60;

	/**
	 * @var int Client ID (resource ID of connection)
	 */
	private readonly int $clientId;

	/**
	 * @var string Address of remote client
	 */
	private readonly string $remoteAddr;

	/**
	 * @var ExpBackoffDelay Backoff reconnect delay
	 */
	private readonly ExpBackoffDelay $reconnectDelay;

	/**
	 * @var TimerInterface|null Reconnect timer
	 */
	private ?TimerInterface $reconnectTimer = null;

	/**
	 * @var WebSocket|null Upstream connection
	 */
	private ?WebSocket $upstream = null;

	/**
	 * @var Closure(int):void|null On session close callback
	 */
	private ?Closure $onSessionClose = null;

	/**
	 * @var bool Session authenticated state
	 */
	private bool $authenticated = false;

	/**
	 * @var int|null Session expiration time
	 */
	private ?int $expiration = null;

	/**
	 * @var bool Indicates that session is closing (do not reconnect, do not handle messages)
	 */
	private bool $closing = false;

	/**
	 * Constructor
	 * @param ConnectionInterface $client Remote client
	 * @param string $upstreamAddr Upstream URL
	 * @param string $apiToken API token for upstream authentication
	 * @param Connector $connector Connector for establishing upstream connection
	 * @param LoggerInterface $logger Logger
	 */
	public function __construct(
		private readonly ConnectionInterface $client,
		private readonly string $upstreamAddr,
		private readonly string $apiToken,
		private readonly Connector $connector,
		private readonly LoggerInterface $logger,
	) {
		// @phpstan-ignore property.notFound
		$this->clientId = $client->resourceId;
		// @phpstan-ignore property.notFound
		$this->remoteAddr = $client->remoteAddress;
		$this->reconnectDelay = new ExpBackoffDelay(self::MAX_RECONNECT_DELAY);
		$this->connect();
	}

	///// PUBLIC API

	/**
	 * Registers external callback to execute when session object is shutting down
	 * @param Closure(int):void $onSessionClose Callback
	 */
	public function registerSessionCloseCallback(callable $onSessionClose): void {
		$this->onSessionClose = $onSessionClose;
	}

	/**
	 * Processes a client message to upstream.
	 *
	 * Before attempting to send them message, the session checks if the message is
	 * a Daemon API message (include message type, and message ID).
	 * The message is only sent if the upstream exists.
	 * The method returns if the upstream object does not exist.
	 *
	 * If the upstream does exist but isn't authenticated, the message is not sent.
	 *
	 * In all error cases, the client is notified.
	 *
	 * @param string $msg Message to send
	 */
	public function handleClientMessage(string $msg): void {
		$json = Json::decode($msg);

		if (!ProxyMessageValidator::isDaemonApiMessage($json)) {
			$this->logger->warning(
				'[{id}|{addr}] Invalid message for upstream, discarding message: {msg}',
				[
					'addr' => $this->remoteAddr,
					'id' => strval($this->clientId),
					'msg' => $msg,
				],
			);
			$this->client->send((new UpstreamRequestInvalid($msg))->toJsonString());
			return;
		}

		$mType = $json->mType;
		$msgId = $json->data->msgId;

		if ($this->upstream === null) {
			$this->logger->error(
				'[{id}|{addr}] Cannot send message to upstream, upstream session does not exist.',
				[
					'addr' => $this->remoteAddr,
					'id' => strval($this->clientId),
				],
			);
			$this->client->send((new UpstreamRequestFailed($mType, $msgId))->toJsonString());
			return;
		}

		if (!$this->authenticated) {
			$this->logger->warning(
				'[{id}|{addr}] Cannot send message to upstream, session not authenticated.',
				[
					'addr' => $this->remoteAddr,
					'id' => strval($this->clientId),
				],
			);
			$this->client->send((new UpstreamRequestFailed($mType, $msgId))->toJsonString());
			return;
		}

		$this->upstream->send($msg);
	}

	/**
	 * Closes session.
	 *
	 * The session object is marked as closing, ensuring other methods do not
	 * perpetuate lifecycle of the session.
	 *
	 * If the upstream connection is not currently established, and session is
	 * waiting to reconnect, the reconnect timer is cancelled and destroyed.
	 *
	 * If the session has an upstream close callback registered, it is called.
	 * Existing upstream connection is closed, and then upstream object is destroyed.
	 *
	 * Finally, the client connection is closed if it still is open.
	 *
	 * @param int $code Close code
	 * @param string $reason Reason
	 */
	public function closeSession(int $code = 1000, string $reason = ''): void {
		// mark session as closing
		$this->closing = true;
		// cancel reconnect timer if remote client closes connection and upstream session is to be re-established
		if ($this->reconnectTimer !== null) {
			Loop::get()->cancelTimer($this->reconnectTimer);
			$this->reconnectTimer = null;
		}
		if ($this->onSessionClose !== null) {
			($this->onSessionClose)($this->clientId);
		}
		// if upstream exists and is connected, close it
		if ($this->upstream !== null) {
			$this->upstream->close($code, $reason);
			$this->upstream = null;
		}
		$this->client->close();
	}

	///// Upstream session management

	/**
	 * Creates connection to upstream.
	 *
	 * The received connector is used to establish connection with upstream.
	 * If the upstream succeeds, onOpen is called to handle success.
	 *
	 * Failure to establish connection is followed by scheduling of reconnect.
	 *
	 * The method does nothing if session mapping object is shutting down.
	 */
	protected function connect(): void {
		if ($this->closing) {
			return;
		}

		($this->connector)($this->upstreamAddr)->then(
			function (WebSocket $upstream): void {
				$this->onOpen($upstream);
			},
			function (Throwable $e): void {
				$this->logger->error(
					'[{id}|{addr}] Failed to establish upstream connection with proxy server for client: {reason}.',
					[
						'addr' => $this->remoteAddr,
						'id' => strval($this->clientId),
						'reason' => $e->getMessage(),
						'code' => strval($e->getCode()),
					],
				);
				$this->reconnect();
			}
		);
	}

	/**
	 * Attempts to authenticate upstream session.
	 *
	 * The method sends authentication message to upstream containing API token.
	 *
	 * This method does nothing if session is already authenticated, or the upstream is not initialized.
	 */
	protected function authenticate(): void {
		if ($this->authenticated) {
			// session already authenticated
			// TODO reauth before session expires, with a new token
			return;
		}

		if ($this->upstream === null) {
			// TODO error handling of some kind
			return;
		}

		$msg = Json::encode(
			[
				'type' => 'auth',
				'token' => $this->apiToken,
			],
		);
		$this->upstream->send($msg);
	}

	///// Upstream event handlers

	/**
	 * Upstream connection open handler.
	 *
	 * The method first checks if the session mapping object has been
	 * attempting to reconnect to upstream, if that is the case,
	 * the reconnect timer is reset as well as the backoff delay generator.
	 *
	 * The new upstream session is stored and has callbacks registered
	 * for handling events.
	 *
	 * Upstream session expects authentication, and so finally,
	 * the authentication method is called.
	 */
	protected function onOpen(WebSocket $upstream): void {
		// cleanup reconnect timer
		if ($this->reconnectTimer !== null) {
			Loop::get()->cancelTimer($this->reconnectTimer);
			$this->reconnectTimer = null;
		}
		// reset reconnect delay on successful connection
		$this->reconnectDelay->reset();
		// store upstream
		$this->upstream = $upstream;
		$this->logger->info(
			'[{id}|{addr}] Connection to upstream established.',
			[
				'addr' => $this->remoteAddr,
				'id' => strval($this->clientId),
			],
		);

		$this->registerCallbacks();
		$this->authenticate();
	}

	/**
	 * Incoming upstream message handler.
	 *
	 * A message from upstream should be either authentication response,
	 * or response from Daemon API server (or asynchronous message).
	 *
	 * If the session has not been previously authenticated, and authentication success
	 * message is received, the session is marked authenticated and expiration is stored.
	 * The client is then notified that upstream is ready to accept Daemon API messasges.
	 * However, if the authentication failed, client is notified of authentication failure.
	 *
	 * If the session has been authenticated and authentication failure message is received,
	 * it means that the session was closed by upstream due to expiration or token revocation.
	 * Client is once again notified of the same.
	 *
	 * In other cases, the message is forwarded to client.
	 *
	 * @param MessageInterface<int, string> $msg Received message
	 */
	protected function onMessage(MessageInterface $msg): void {
		// do not process any messages from upstream if client is closing session
		if ($this->closing) {
			return;
		}

		$this->logger->debug(
			'[{id}|{addr}] Incoming message from upstream for client: {message}',
			[
				'addr' => $this->remoteAddr,
				'id' => strval($this->clientId),
				'message' => $msg,
			],
		);

		try {
			$json = Json::decode($msg->getPayload());

			// session to upstream authenticated
			if (!$this->authenticated && ProxyMessageValidator::isAuthSuccessMessage($json)) {
				$this->logger->info(
					'[{id}|{addr}] Upstream session successfully authenticated, expiration: {expiration}',
					[
						'addr' => $this->remoteAddr,
						'id' => strval($this->clientId),
						'expiration' => $json->expiration,
					],
				);
				$this->authenticated = true;
				$this->expiration = $json->expiration;
				$this->client->send((new UpstreamReady())->toJsonString());
				return;
			}

			// session to upstream could not be authenticated
			if (!$this->authenticated && ProxyMessageValidator::isAuthErrorMessage($json)) {
				$this->logger->warning(
					'[{id}|{addr}] Failed to authenticate upstream session: {reason}({code})',
					[
						'addr' => $this->remoteAddr,
						'id' => strval($this->clientId),
						'reason' => $json->error,
						'code' => $json->code,
					],
				);
				$this->client->send((new UpstreamAuthFailed($json->code))->toJsonString());
				return;
			}

			// authenticated session ended due to expired or revoked token, or another unexpected reason
			if ($this->authenticated && ProxyMessageValidator::isAuthErrorMessage($json)) {
				$this->logger->warning(
					'[{id}|{addr}] Upstream session closed for authentication reasons: {reason}({code})',
					[
						'addr' => $this->remoteAddr,
						'id' => strval($this->clientId),
						'reason' => $json->error,
						'code' => $json->code,
					],
				);
				$this->client->send((new UpstreamAuthFailed($json->code))->toJsonString());
				return;
			}

			$this->client->send((new UpstreamResponse($json))->toJsonString());
		} catch (JsonException $e) {
			$this->logger->error(
				'[{id}|{addr}] Received invalid JSON message: {message}',
				[
					'addr' => $this->remoteAddr,
					'id' => strval($this->clientId),
					'message' => $msg,
				],
			);
		}
	}

	/**
	 * Upstream connection close handler.
	 *
	 * When upstream connection closes, it should be re-established,
	 * unless the session mapping object is in process of shutting down.
	 *
	 * Client connection is notified of upstream connection closure and reconnect
	 * to upstream is scheduled.
	 *
	 * @param int $code Close code
	 * @param string $reason Reason
	 */
	protected function onClose(int $code, string $reason): void {
		$this->logger->info(
			'[{id}|{addr}] Upstream connection closed: {reason}({code}}',
			[
				'addr' => $this->remoteAddr,
				'id' => strval($this->clientId),
				'reason' => $reason,
				'code' => strval($code),
			],
		);

		$this->resetSession();
		$this->client->send((new UpstreamDisconnected())->toJsonString());
		if (!$this->closing) {
			$this->reconnect();
		}
	}

	/**
	 * Upstream connection error handler.
	 *
	 * When an error occurs during the upstream connection lifetime,
	 * it is unusable and closed. The session mapping object resets upstream
	 * session data and notifies the client of upstream connection closure.
	 *
	 * If the session object is not in process of shutting down, reconnect is scheduled.
	 *
	 * @param Throwable $e Error
	 */
	protected function onError(Throwable $e): void {
		$this->logger->error(
			'[{id}|{addr}] Connction to upstream lost: {reason}({code}}',
			[
				'addr' => $this->remoteAddr,
				'id' => strval($this->clientId),
				'reason' => $e->getMessage(),
				'code' => strval($e->getCode()),
			],
		);

		$this->resetSession();
		$this->client->send((new UpstreamDisconnected())->toJsonString());
		if (!$this->closing) {
			$this->reconnect();
		}
	}

	///// Private methods

	/**
	 * Registers callbacks to upstream session.
	 */
	private function registerCallbacks(): void {
		$this->upstream->on('message', function (MessageInterface $msg): void {
			$this->onMessage($msg);
		});
		$this->upstream->on('close', function (int $code, string $reason): void {
			$this->onClose($code, $reason);
		});
		$this->upstream->on('error', function (Throwable $e): void {
			$this->onError($e);
		});
	}

	/**
	 * Schedules a reconnect to upstream.
	 *
	 * The proxy session uses exponential backoff delay to schedule
	 * reconnecting to upstream (establishing session with upstream for the client).
	 *
	 * Once the reconnect is scheduled, client is notified of the planned reconnect with delay.
	 */
	private function reconnect(): void {
		$delay = $this->reconnectDelay->getNext();
		$this->logger->debug(
			'[{id}|{addr}] Reconnect to upstream scheduled in {delay} seconds.',
			[
				'addr' => $this->remoteAddr,
				'id' => strval($this->clientId),
				'delay' => strval($delay),
			],
		);
		$this->reconnectTimer = Loop::get()->addTimer($delay, function (): void {
			$this->logger->info(
				'[{id}|{addr}] Reconnecting to upstream.',
				[
					'addr' => $this->remoteAddr,
					'id' => strval($this->clientId),
				],
			);
			$this->connect();
		});
		$this->client->send((new UpstreamReconnecting($this->reconnectDelay->getCounter(), $delay))->toJsonString());
	}

	/**
	 * Rests upstream part of the proxy session.
	 *
	 * The method destroys upstream object, sets sessions as unauthenticated and clears expiration.
	 */
	private function resetSession(): void {
		$this->upstream = null;
		$this->authenticated = false;
		$this->expiration = null;
	}

}
