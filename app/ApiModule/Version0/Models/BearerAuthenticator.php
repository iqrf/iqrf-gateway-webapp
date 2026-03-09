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

namespace App\ApiModule\Version0\Models;

use App\Models\Database\Entities\ApiKey;
use App\Models\Database\Entities\ApiKeyLegacy;
use App\Models\Database\Entities\User;
use App\Models\Database\EntityManager;
use Contributte\Middlewares\Security\IAuthenticator;
use InvalidArgumentException;
use Nette\Utils\Strings;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class BearerAuthenticator implements IAuthenticator {

	/**
	 * Constructor
	 * @param JwtAuthenticator $jwtAuthenticator JWT authenticator
	 * @param EntityManager $entityManager Entity manager
	 */
	public function __construct(
		private readonly JwtAuthenticator $jwtAuthenticator,
		private readonly EntityManager $entityManager,
	) {
	}

	/**
	 * Authenticates the application or user
	 * @param ServerRequestInterface $request HTTP request
	 * @return ApiKeyLegacy|ApiKey|User|null API key or user entity
	 * @throws InvalidArgumentException
	 */
	public function authenticate(ServerRequestInterface $request): ApiKeyLegacy|ApiKey|User|null {
		$header = $request->getHeader('Authorization')[0] ?? '';
		$token = $this->parseAuthorizationHeader($header);
		if ($token === null) {
			return null;
		}
		// Authenticate with API key
		// TODO: UPDATE REGEX TO BE MORE STRICT, 0-2x '=' -> 44, [..]{43}=, [..]{42}={2}..
		if (Strings::match($token, '~^webapp;[0-9]+;([0-9A-Za-z+/=]{44})$~') !== null) {
			return $this->authenticateApp($token);
		}
		// Authentication with legacy API key
		if (Strings::match($token, '#^[./A-Za-z0-9]{22}\.[A-Za-z0-9+/=]{44}$#') !== null) {
			return $this->authenticateAppLegacy($token);
		}
		return $this->authenticateUser($token);
	}

	/**
	 * Authenticates the application with API key
	 * @param string $key API key
	 * @return ApiKey|null API key entity
	 */
	public function authenticateApp(string $key): ?ApiKey {
		if ($key === '') {
			return null;
		}
		$keyTokens = explode(';', $key, 3);
		$id = intval($keyTokens[1]);
		try {
			$repository = $this->entityManager->getApiKeyRepository();
			$apiKey = $repository->find($id);
			if ($apiKey instanceof ApiKey && $apiKey->verify($keyTokens[2])) {
				return $apiKey;
			}
			return null;
		} catch (Throwable) {
			return null;
		}
	}

	/**
	 * Authenticates the application with legacy API key
	 * @param string $key API key
	 * @return ApiKeyLegacy|null API key entity
	 */
	public function authenticateAppLegacy(string $key): ?ApiKeyLegacy {
		$repository = $this->entityManager->getApiKeyLegacyRepository();
		$salt = Strings::substring($key, 0, 22);
		$apiKey = $repository->findOneBySalt($salt);
		if ($apiKey instanceof ApiKeyLegacy && $apiKey->verify($key)) {
			return $apiKey;
		}
		return null;
	}

	/**
	 * Authenticates the user
	 * @param string $jwt JWT
	 * @return User|null User entity
	 * @throws InvalidArgumentException
	 */
	public function authenticateUser(string $jwt): ?User {
		return $this->jwtAuthenticator->authenticate($jwt);
	}

	/**
	 * Parses the authorization header
	 * @param string $header Authorization header
	 * @return string|null JWT
	 */
	public function parseAuthorizationHeader(string $header): ?string {
		if (!str_starts_with($header, 'Bearer')) {
			return null;
		}
		$str = Strings::substring($header, 7);
		if ($str === '') {
			return null;
		}
		return $str;
	}

}
