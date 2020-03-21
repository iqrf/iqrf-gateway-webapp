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

use App\ApiModule\Version0\RequestAttributes;
use Contributte\Middlewares\IMiddleware;
use Contributte\Middlewares\Security\IAuthenticator;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Authentication middleware
 */
class AuthenticationMiddleware implements IMiddleware {

	/**
	 * Whitelisted paths
	 */
	private const WHITELISTED_PATHS = [
		'/api/v0/openapi',
		'/api/v0/user/signIn',
	];

	/**
	 * @var IAuthenticator Authenticator
	 */
	private $authenticator;

	/**
	 * Constructor
	 * @param IAuthenticator $authenticator Authenticator
	 */
	public function __construct(IAuthenticator $authenticator) {
		$this->authenticator = $authenticator;
	}

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface {
		if ($this->isWhitelisted($request)) {
			// Pass to next middleware
			return $next($request, $response);
		}
		$user = $this->authenticator->authenticate($request);
		// If we have a identity, then go to next middleware, otherwise stop and return current response
		if ($user === null) {
			$response->getBody()->write(Json::encode([
				'error' => 'Client authentication failed',
			]));
			return $response->withStatus(401)
				->withHeader('WWW-Authenticate', 'Bearer');
		}
		// Add info about current logged user to request attributes
		$request = $request->withAttribute(RequestAttributes::APP_LOGGED_USER, $user);
		// Pass to next middleware
		return $next($request, $response);
	}

	/**
	 * Checks if the path is whitelisted
	 * @param ServerRequestInterface $request API request
	 * @return bool Is the path whitelisted?
	 */
	protected function isWhitelisted(ServerRequestInterface $request): bool {
		foreach (self::WHITELISTED_PATHS as $path) {
			if (Strings::startsWith($request->getUri()->getPath(), $path)) {
				return true;
			}
		}
		return false;
	}

}
