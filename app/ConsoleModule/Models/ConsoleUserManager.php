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

/**
 * Tool for managing users from CLI
 */
class ConsoleUserManager extends UserManager {

	/**
	 * Gets information about the user from the username
	 * @param string $username Username
	 * @return mixed[]|null Information about the user
	 */
	public function getUser(?string $username): ?array {
		$user = $this->entityManager->getUserRepository()->findOneByUserName($username);
		if ($user === null) {
			return null;
		}
		$array = $user->toArray();
		unset($array['password']);
		return $array;
	}

	/**
	 * Checks if the username is unique
	 * @param string $username Username to check
	 * @return bool Is username unique?
	 */
	public function uniqueUserName(string $username): bool {
		$user = $this->entityManager->getUserRepository()->findOneByUserName($username);
		return $user === null;
	}

	/**
	 * Lists user names of all webapp's users
	 * @return mixed[] User names of all webapp's users
	 */
	public function listUserNames(): array {
		$users = [];
		foreach ($this->getUsers() as $user) {
			$users[] = $user['username'];
		}
		return $users;
	}

}
