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

namespace App\CoreModule\Models;

use App\Models\Database\EntityManager;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\IIdentity;
use Nette\SmartObject;

/**
 * Custom Authenticator
 */
class AppAuthenticator implements IAuthenticator {

	use SmartObject;

	/**
	 * @var EntityManager Entity manager
	 */
	private $entityManager;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 */
	public function __construct(EntityManager $entityManager) {
		$this->entityManager = $entityManager;
	}

	/**
	 * Authenticates the user
	 * @param string[] $credentials Authentication credentials
	 * @return IIdentity Nette Identity
	 * @throws AuthenticationException
	 */
	public function authenticate(array $credentials): IIdentity {
		[$username, $password] = $credentials;
		$user = $this->entityManager->getUserRepository()->findOneByUserName($username);
		if ($user === null) {
			throw new AuthenticationException('User not found.');
		}
		if (!password_verify($password, $user->getPassword())) {
			throw new AuthenticationException('Invalid password.');
		}
		$this->entityManager->flush();
		return $user->toIdentity();
	}

}
