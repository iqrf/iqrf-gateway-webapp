<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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

namespace App\ApiModule\Version0\Middlewares;

use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\RequestAttributes;
use App\Models\Database\Entities\ApiKey;
use App\Models\Database\Entities\User;
use App\Models\Database\EntityManager;
use Contributte\Middlewares\IMiddleware;
use Contributte\Middlewares\Security\IAuthenticator;
use InvalidArgumentException;
use Lcobucci\Jose\Parsing\Exception as JwtParsingException;
use Nette\Utils\Json;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function array_search;

/**
 * Authentication middleware
 */
class AuthenticationMiddleware implements IMiddleware {

	/**
	 * Whitelisted installer paths
	 */
	private const INSTALLER_PATHS = [
		'/api/v0/gateway/info',
		'/api/v0/installation/rootpass',
		'/api/v0/services/ssh',
		'/api/v0/services/ssh/disable',
		'/api/v0/services/ssh/enable',
		'/api/v0/services/ssh/start',
		'/api/v0/services/ssh/stop',
		'/api/v0/services/ssh/restart',
		'/api/v0/users',
	];

	/**
	 * Whitelisted paths
	 */
	private const WHITELISTED_PATHS = [
		'/api/v0/installation',
		'/api/v0/features',
		'/api/v0/openapi',
		'/api/v0/user/signIn',
	];

	/**
	 * @var IAuthenticator Authenticator
	 */
	private $authenticator;

	/**
	 * @var EntityManager Database entity manager
	 */
	private $entityManager;

	/**
	 * Constructor
	 * @param IAuthenticator $authenticator Authenticator
	 * @param EntityManager $entityManager Database entity manager
	 */
	public function __construct(IAuthenticator $authenticator, EntityManager $entityManager) {
		$this->authenticator = $authenticator;
		$this->entityManager = $entityManager;
	}

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface {
		if ($this->isWhitelisted($request)) {
			// Pass to next middleware
			return $next($request, $response);
		}
		try {
			$identity = $this->authenticator->authenticate($request);
		} catch (JwtParsingException | InvalidArgumentException $e) {
			return $this->createUnauthorizedResponse($response, 'Invalid JWT');
		}
		// If we have a identity, then go to next middleware, otherwise stop and return current response
		if ($identity === null) {
			return $this->createUnauthorizedResponse($response, 'Client authentication failed');
		}
		if ($identity instanceof User) {
			// Add info about current logged user to request attributes
			$request = $request->withAttribute(RequestAttributes::APP_LOGGED_USER, $identity);
		} elseif ($identity instanceof ApiKey) {
			// Add info about current logged application to request attributes
			$request = $request->withAttribute(RequestAttributes::APP_LOGGED_APP, $identity);
		}
		// Pass to next middleware
		return $next($request, $response);
	}

	/**
	 * Creates unauthorized response
	 * @param ResponseInterface $response Response to modify
	 * @param string $message Message
	 * @return ResponseInterface Response
	 */
	private function createUnauthorizedResponse(ResponseInterface $response, string $message): ResponseInterface {
		$json = Json::encode(['error' => $message]);
		$response->getBody()->write($json);
		return $response->withStatus(ApiResponse::S401_UNAUTHORIZED)
			->withHeader('WWW-Authenticate', 'Bearer')
			->withHeader('Content-Type', 'application/json');
	}

	/**
	 * Checks if the path is whitelisted
	 * @param ServerRequestInterface $request API request
	 * @return bool Is the path whitelisted?
	 */
	protected function isWhitelisted(ServerRequestInterface $request): bool {
		$requestUrl = rtrim($request->getUri()->getPath(), '/');
		if (array_search($requestUrl, self::WHITELISTED_PATHS, true) !== false) {
			return true;
		}
		return ($this->entityManager->getUserRepository()->count([]) === 0) &&
			(array_search($requestUrl, self::INSTALLER_PATHS, true) !== false);
	}

}
