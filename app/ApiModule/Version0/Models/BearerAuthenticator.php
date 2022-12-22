<?php

/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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
use App\Models\Database\Entities\User;
use App\Models\Database\EntityManager;
use Contributte\Middlewares\Security\IAuthenticator;
use DateTimeImmutable;
use InvalidArgumentException;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Nette\Utils\Strings;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class BearerAuthenticator implements IAuthenticator {

	/**
	 * @var EntityManager Entity manager
	 */
	private $entityManager;

	/**
	 * @var Configuration JWT configuration
	 */
	private $configuration;

	/**
	 * Constructor
	 * @param JwtConfigurator $configurator JWT configurator
	 * @param EntityManager $entityManager Entity manager
	 */
	public function __construct(JwtConfigurator $configurator, EntityManager $entityManager) {
		$this->configuration = $configurator->create();
		$this->entityManager = $entityManager;
	}

	/**
	 * @inheritDoc
	 * @throws InvalidArgumentException
	 */
	public function authenticate(ServerRequestInterface $request) {
		$header = $request->getHeader('Authorization')[0] ?? '';
		$token = $this->parseAuthorizationHeader($header);
		if ($token === null) {
			return null;
		}
		if (Strings::match($token, '~^[./A-Za-z0-9]{22}\.[A-Za-z0-9+/=]{44}$~') !== null) {
			return $this->authenticateApp($token);
		}
		return $this->authenticateUser($token);
	}

	/**
	 * @param string $key API key
	 * @return ApiKey|null API key entity
	 */
	public function authenticateApp(string $key): ?ApiKey {
		$repository = $this->entityManager->getApiKeyRepository();
		$salt = Strings::substring($key, 0, 22);
		$apiKey = $repository->findOneBySalt($salt);
		return $apiKey->verify($key) ? $apiKey : null;
	}

	/**
	 * Authenticates the user
	 * @param string $jwt JWT
	 * @return User|null User entity
	 * @throws InvalidArgumentException
	 */
	public function authenticateUser(string $jwt): ?User {
		$token = $this->configuration->parser()->parse($jwt);
		if (!$this->isJwtValid($token)) {
			return null;
		}
		try {
			$repository = $this->entityManager->getUserRepository();
			$id = $token->claims()->get('uid');
			$user = $repository->find($id);
			if (!($user instanceof User)) {
				return null;
			}
			return $user;
		} catch (Throwable $e) {
			return null;
		}
	}

	/**
	 * Validates JWT
	 * @param Plain $token JWT to validate
	 * @return bool Is JWT valid?
	 */
	private function isJwtValid(Plain $token): bool {
		$hostname = gethostname();
		$now = new DateTimeImmutable();
		$validator = $this->configuration->validator();
		$signer = $this->configuration->signer();
		$verificationKey = $this->configuration->verificationKey();
		$signedWith = new SignedWith($signer, $verificationKey);
		return $validator->validate($token, $signedWith) &&
			!$token->isExpired($now) &&
			$token->claims()->has('uid') &&
			$token->hasBeenIssuedBefore($now) &&
			($hostname === false || $token->hasBeenIssuedBy($hostname) &&
				$token->isIdentifiedBy($hostname));
	}

	/**
	 * Parses the authorization header
	 * @param string $header Authorization header
	 * @return string|null JWT
	 */
	public function parseAuthorizationHeader(string $header): ?string {
		if (!Strings::startsWith($header, 'Bearer')) {
			return null;
		}
		$str = Strings::substring($header, 7);
		if ($str === '') {
			return null;
		}
		return $str;
	}

}
