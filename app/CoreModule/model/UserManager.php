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

namespace App\CoreModule\Model;

use App\CoreModule\Exception\InvalidPasswordException;
use App\CoreModule\Exception\UsernameAlreadyExistsException;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\SmartObject;

/**
 * Tool for managing users
 */
class UserManager {

	use SmartObject;

	/**
	 * @var Selection Database table selection
	 */
	private $table;

	/**
	 * Constructor
	 * @param Context $database Database context
	 */
	public function __construct(Context $database) {
		$this->table = $database->table('users');
	}

	/**
	 * Change user's password
	 * @param int $id User ID
	 * @param string $oldPassword Old password
	 * @param string $newPassword New password
	 * @throws InvalidPasswordException
	 */
	public function changePassword(int $id, string $oldPassword, string $newPassword): void {
		$row = $this->table->where('id', $id)->fetch();
		if (!password_verify($oldPassword, $row['password'])) {
			throw new InvalidPasswordException();
		}
		$this->editPassword($id, $newPassword);
	}

	/**
	 * Delete the user
	 * @param int $id User ID
	 */
	public function delete(int $id): void {
		$this->table->where('id', $id)->delete();
	}

	/**
	 * Edit the user
	 * @param int $id User ID
	 * @param string $username New username
	 * @param string $role New user role
	 * @param string $language New user's language
	 * @throws UsernameAlreadyExistsException
	 */
	public function edit(int $id, string $username, string $role, string $language): void {
		$row = $this->table->where('username', $username)->fetch();
		if ($row && $row['id'] !== $id) {
			throw new UsernameAlreadyExistsException();
		}
		$data = [
			'username' => $username,
			'role' => $role,
			'language' => $language,
		];
		$this->table->where('id', $id)->update($data);
	}

	/**
	 * Edit rhe user's password
	 * @param int $id User ID
	 * @param string $password New User's password
	 */
	public function editPassword(int $id, string $password): void {
		$data = ['password' => password_hash($password, PASSWORD_DEFAULT)];
		$this->table->where('id', $id)->update($data);
	}

	/**
	 * Get information about the user
	 * @param int $id User ID
	 * @return array|null Information about the user or null
	 */
	public function getInfo(int $id): ?array {
		$row = $this->table->get($id);
		if ($row instanceof ActiveRow) {
			return $row->toArray();
		}
		return null;
	}

	/**
	 * Get all registered users
	 * @return array Registered users
	 */
	public function getUsers(): array {
		$users = [];
		foreach ($this->table->fetchAll() as $user) {
			$users[] = $user->toArray();
		}
		return $users;
	}

	/**
	 * Register a new user
	 * @param string $username Username
	 * @param string $password Password
	 * @param string $role User's role
	 * @param string $language User's language
	 * @throws UsernameAlreadyExistsException
	 */
	public function register(string $username, string $password, string $role, string $language): void {
		$row = $this->table->where('username', $username)->fetch();
		if ($row) {
			throw new UsernameAlreadyExistsException();
		}
		$data = [
			'username' => $username,
			'password' => password_hash($password, PASSWORD_DEFAULT),
			'role' => $role,
			'language' => $language,
		];
		$this->table->insert($data);
	}

}
