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

namespace App\ConsoleModule\Models;

use App\CoreModule\Models\UserManager;
use Nette\SmartObject;

/**
 * Tool for managing users from CLI
 */
class ConsoleUserManager extends UserManager {

	use SmartObject;

	/**
	 * Get information about the user from username
	 * @param string|null $username Username
	 * @return mixed[]|null Information about the user
	 */
	public function getUser(?string $username): ?array {
		foreach ($this->getUsers() as $user) {
			if ($user['username'] === $username) {
				return $user;
			}
		}
		return null;
	}

	/**
	 * Check if the username is unique
	 * @param string|null $username Username to check
	 * @return bool Is username unique?
	 */
	public function uniqueUserName(?string $username): bool {
		if ($username === null) {
			return false;
		}
		foreach ($this->getUsers() as $user) {
			if ($user['username'] === $username) {
				return false;
			}
		}
		return true;
	}

	/**
	 * List all registered users
	 * @return mixed[] Registered users
	 */
	public function listUsers(): array {
		$users = $this->getUsers();
		$this->removeHashes($users);
		return $users;
	}

	/**
	 * List user names of all webapp's users
	 * @return mixed[] User names of all webapp's users
	 */
	public function listUserNames(): array {
		$users = [];
		foreach ($this->getUsers() as $user) {
			$users[] = $user['username'];
		}
		return $users;
	}

	/**
	 * Remove hashes from the information about the users
	 * @param mixed[] $users Information about the users
	 */
	private function removeHashes(array &$users): void {
		foreach ($users as &$user) {
			unset($user['password']);
		}
	}

}
