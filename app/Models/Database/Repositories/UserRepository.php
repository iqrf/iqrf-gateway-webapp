<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

namespace App\Models\Database\Repositories;

use App\Models\Database\Entities\User;
use Doctrine\ORM\EntityRepository;

/**
 * User repository
 */
class UserRepository extends EntityRepository {

	/**
	 * Finds the user by username
	 * @param string $userName User name
	 * @return User|null User entity
	 */
	public function findOneByUserName(string $userName): ?User {
		$user = $this->findOneBy(['username' => $userName]);
		assert($user instanceof User || $user === null);
		return $user;
	}

	/**
	 * Lists user names
	 * @return array<string> User names
	 */
	public function listUserNames(): array {
		$usernames = [];
		foreach ($this->findAll() as $user) {
			assert($user instanceof User);
			$usernames[] = $user->getUserName();
		}
		return $usernames;
	}

}
