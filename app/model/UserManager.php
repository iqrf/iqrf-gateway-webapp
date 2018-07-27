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

use App\Model\InvalidPassword;
use App\Model\UsernameAlreadyExists;
use Nette;
use Nette\Database\Context;
use Nette\Database\Table\Selection;

/**
 * Tool for managing users.
 */
class UserManager {

	use Nette\SmartObject;

	/**
	 * @var Context Database context
	 */
	private $database;

	/**
	 * @var Selection Database table selection
	 */
	private $table;

	/**
	 * Constructor
	 * @param Context $database Database contaxt
	 */
	public function __construct(Context $database) {
		$this->database = $database;
		$this->table = $this->database->table('users');
	}

	/**
	 * Change user's language
	 * @param int $userId User ID
	 * @param string $language New user's language
	 */
	public function changeLanguage(int $userId, string $language) {
		$this->table->where('id', $userId)->update(['language' => $language]);
	}

	/**
	 * Change user's password
	 * @param int $userId User ID
	 * @param string $oldPassword Old password
	 * @param string $newPassword New password
	 * @throws InvalidPassword
	 * @throws MismatchedPasswords
	 */
	public function changePassword(int $userId, string $oldPassword, string $newPassword) {
		$row = $this->table->where('id', $userId)->fetch();
		if (!password_verify($oldPassword, $row->password)) {
			throw new InvalidPassword();
		}
		$data = ['password' => password_hash($newPassword, PASSWORD_DEFAULT)];
		$this->table->where('id', $userId)->update($data);
	}

	/**
	 * Change username
	 * @param int $userId User ID
	 * @param string $newUsername New username
	 * @throws UsernameAlreadyExists
	 */
	public function changeUsername(int $userId, string $newUsername) {
		$row = $this->table->where('username', $newUsername)->fetch();
		if ($row) {
			throw new UsernameAlreadyExists();
		}
		$this->table->where('id', $userId)->update(['username' => $newUsername]);
	}

	/**
	 * Change User type
	 * @param int $userId User ID
	 * @param string $userType New User type
	 */
	public function changeUserType(int $userId, string $userType) {
		$this->table->where('id', $userId)->update(['user_type' => $userType]);
	}

	/**
	 * Delete User
	 * @param int $userId User ID
	 */
	public function delete(int $userId) {
		$this->table->where('id', $userId)->delete();
	}

	/**
	 * Register new user
	 * @param string $username Username
	 * @param string $password Password
	 * @param string $userType User type
	 * @param string $language User's language
	 * @throws UsernameAlreadyExists
	 */
	public function register(string $username, string $password, string $userType, string $language) {
		$row = $this->table->where('username', $username)->fetch();
		if ($row) {
			throw new UsernameAlreadyExists();
		}
		$data = [
			'username' => $username,
			'password' => password_hash($password, PASSWORD_DEFAULT),
			'user_type' => $userType,
			'language' => $language,
		];
		$this->table->insert($data);
	}

}
