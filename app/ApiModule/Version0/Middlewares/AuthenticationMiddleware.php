<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Authentication middleware
 */
class AuthenticationMiddleware implements IMiddleware {

	/**
	 * Whitelisted installer paths
	 */
	private const INSTALLER_PATHS = [
		'/api/v0/gateway/info',
		'/api/v0/users',
		'/api/v0/security/users',
		'/api/v0/maintenance/restore',
	];

	/**
	 * Whitelisted paths
	 */
	private const WHITELISTED_PATHS = [
		'/api/v0/installation',
		'/api/v0/features',
		'/api/v0/openapi',
		'/api/v0/user/password/recovery',
		'/api/v0/user/signIn',
	];

	/**
	 * Constructor
	 * @param IAuthenticator $authenticator Authenticator
	 * @param EntityManager $entityManager Database entity manager
	 */
	public function __construct(
		private readonly IAuthenticator $authenticator,
		private readonly EntityManager $entityManager,
	) {
	}

	/**
	 * Checks if the path is whitelisted
	 * @param ServerRequestInterface $request API request
	 * @return bool Is the path whitelisted?
	 */
	protected function isWhitelisted(ServerRequestInterface $request): bool {
		$requestUrl = rtrim($request->getUri()->getPath(), '/');
		if (in_array($requestUrl, self::WHITELISTED_PATHS, true) ||
			Strings::match($requestUrl, '#^/api/v0/user/verify/[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-4[0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$#') !== null ||
			Strings::match($requestUrl, '#^/api/v0/user/password/recovery/[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-4[0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$#') !== null ||
			Strings::match($requestUrl, '~^/api/v0/openapi/schemas/.*$~') !== null) {
			return true;
		}
		return ($this->entityManager->getUserRepository()->count([]) === 0) &&
			(in_array($requestUrl, self::INSTALLER_PATHS, true));
	}

	/**
	 * Creates unauthorized response
	 * @param ResponseInterface $response Response to modify
	 * @param string $message Message
	 * @return ResponseInterface Response
	 */
	private function createUnauthorizedResponse(ResponseInterface $response, string $message): ResponseInterface {
		$json = Json::encode([
			'status' => 'error',
			'code' => 401,
			'message' => $message,
		]);
		$response->getBody()->write($json);
		return $response->withStatus(ApiResponse::S401_UNAUTHORIZED)
			->withHeader('WWW-Authenticate', 'Bearer')
			->withHeader('Content-Type', 'application/json');
	}

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface {
		if ($this->isWhitelisted($request)) {
			// Pass to next middleware
			return $next($request, $response);
		}
		try {
			$identity = $this->authenticator->authenticate($request);
		} catch (InvalidArgumentException $e) {
			return $this->createUnauthorizedResponse($response, 'Invalid JWT');
		}
		// If we have an identity, then go to next middleware, otherwise stop and return current response
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

}
