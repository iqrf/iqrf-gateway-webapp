<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
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

namespace App\Model;

use Nette;
use Nette\Database\Context;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;

/**
 * Custom Authenticator
 */
class AppAuthenticator implements IAuthenticator {

	use Nette\SmartObject;

	/**
	 * @var Context Database context
	 */
	private $database;

	/**
	 * Constructor
	 * @param Context $database Database contaxt
	 */
	public function __construct(Context $database) {
		$this->database = $database;
	}

	/**
	 * Authenticate the user
	 * @param array $credentials Authentication credentials
	 * @return Identity Nette Identity
	 * @throws AuthenticationException
	 */
	public function authenticate(array $credentials) {
		list($username, $password) = $credentials;
		$row = $this->database->table('users')->where('username', $username)->fetch();
		if (!$row) {
			throw new AuthenticationException('User not found.');
		}
		if (!password_verify($password, $row['password'])) {
			throw new AuthenticationException('Invalid password.');
		}
		$data = ['username' => $row['username'], 'language' => $row['language']];
		return new Identity($row['id'], $row['user_type'], $data);
	}

}
