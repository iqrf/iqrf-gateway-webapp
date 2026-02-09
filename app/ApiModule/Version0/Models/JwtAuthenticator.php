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

use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiResponse;
use App\GatewayModule\Models\Utils\GatewayInfoUtil;
use App\Models\Database\Entities\User;
use App\Models\Database\EntityManager;
use DateTimeImmutable;
use InvalidArgumentException;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\HasClaim;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Throwable;

class JwtAuthenticator {

	/**
	 * JWT claim audience value
	 */
	public const AUDIENCE = 'iqrf-gateway-webapp';

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
	 * Authenticates the user
	 * @param string $jwt JWT
	 * @return User|null User entity
	 * @throws InvalidArgumentException
	 */
	public function authenticate(string $jwt): ?User {
		if ($jwt === '') {
			return null;
		}
		$parser = $this->jwtConfigurator->create()->parser();
		$token = $parser->parse($jwt);
		assert($token instanceof Plain);
		if (!$this->validate($token)) {
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
	 * Creates a new JWT token
	 * @param User $user User
	 * @return string JWT token
	 */
	public function createToken(User $user): string {
		try {
			$now = new DateTimeImmutable();
			$us = $now->format('u');
			$now = $now->modify('-' . $us . ' usec');
		} catch (Throwable $e) {
			throw new ServerErrorException('Date creation error', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
		$configuration = $this->jwtConfigurator->create();
		$gwId = $this->gatewayInfo->getIdNullable();
		$builder = $configuration->builder()
			->issuedAt($now)
			->canOnlyBeUsedAfter($now)
			->expiresAt($now->modify('+90 min'))
			->permittedFor(self::AUDIENCE)
			->withClaim('uid', $user->getId());
		if ($gwId !== null && $gwId !== '') {
			$builder = $builder->issuedBy($gwId);
		}
		return $builder->getToken(
			signer: $configuration->signer(),
			key: $configuration->signingKey()
		)->toString();
	}

	/**
	 * Validates JWT
	 * @param Plain $token JWT to validate
	 * @return bool Is JWT valid?
	 */
	private function validate(Plain $token): bool {
		$configuration = $this->jwtConfigurator->create();
		$gwId = $this->gatewayInfo->getIdNullable();
		$validator = $configuration->validator();
		return $validator->validate(
			$token,
			new SignedWith(
				signer: $configuration->signer(),
				key: $configuration->verificationKey(),
			),
			new PermittedFor(self::AUDIENCE),
			new StrictValidAt(SystemClock::fromSystemTimezone()),
			new HasClaim('uid')
		) &&
			($gwId === null || $gwId === '' || ($token->hasBeenIssuedBy($gwId)));
	}

}
