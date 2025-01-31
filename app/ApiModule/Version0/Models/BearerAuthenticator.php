<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

use App\GatewayModule\Models\Utils\GatewayInfoUtil;
use App\Models\Database\Entities\ApiKey;
use App\Models\Database\Entities\User;
use App\Models\Database\EntityManager;
use Contributte\Middlewares\Security\IAuthenticator;
use DateTimeImmutable;
use InvalidArgumentException;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Nette\Utils\Strings;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class BearerAuthenticator implements IAuthenticator {

	/**
	 * Constructor
	 * @param JwtConfigurator $jwtConfigurator JWT configurator
	 * @param EntityManager $entityManager Entity manager
	 * @param GatewayInfoUtil $gatewayInfo Gateway info
	 */
	public function __construct(
		private readonly JwtConfigurator $jwtConfigurator,
		private readonly EntityManager $entityManager,
		private readonly GatewayInfoUtil $gatewayInfo,
	) {
	}

	/**
	 * Authenticates the application or user
	 * @param ServerRequestInterface $request HTTP request
	 * @return ApiKey|User|null API key or user entity
	 * @throws InvalidArgumentException
	 */
	public function authenticate(ServerRequestInterface $request): ApiKey|User|null {
		$header = $request->getHeader('Authorization')[0] ?? '';
		$token = $this->parseAuthorizationHeader($header);
		if ($token === null) {
			return null;
		}
		if (Strings::match($token, '#^[./A-Za-z0-9]{22}\.[A-Za-z0-9+/=]{44}$#') !== null) {
			return $this->authenticateApp($token);
		}
		return $this->authenticateUser($token);
	}

	/**
	 * Authenticates the application
	 * @param string $key API key
	 * @return ApiKey|null API key entity
	 */
	public function authenticateApp(string $key): ?ApiKey {
		$repository = $this->entityManager->getApiKeyRepository();
		$salt = Strings::substring($key, 0, 22);
		$apiKey = $repository->findOneBySalt($salt);
		if ($apiKey instanceof ApiKey && $apiKey->verify($key)) {
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
		$parser = $this->jwtConfigurator->create()->parser();
		$token = $parser->parse($jwt);
		assert($token instanceof Plain);
		if (!$this->isJwtValid($token)) {
			return null;
		}
		try {
			$repository = $this->entityManager->getUserRepository();
			$id = $token->claims()->get('uid');
			return $repository->find($id);
		} catch (Throwable) {
			return null;
		}
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

	/**
	 * Validates JWT
	 * @param Plain $token JWT to validate
	 * @return bool Is JWT valid?
	 */
	private function isJwtValid(Plain $token): bool {
		$gwid = $this->gatewayInfo->getIdNullable();
		$now = new DateTimeImmutable();
		$configuration = $this->jwtConfigurator->create();
		$validator = $configuration->validator();
		$signer = $configuration->signer();
		$verificationKey = $configuration->verificationKey();
		$signedWith = new SignedWith($signer, $verificationKey);
		return $validator->validate($token, $signedWith) &&
			!$token->isExpired($now) &&
			$token->claims()->has('uid') &&
			$token->hasBeenIssuedBefore($now) &&
			($gwid === null || ($token->hasBeenIssuedBy($gwid) && $token->isIdentifiedBy($gwid)));
	}

}
