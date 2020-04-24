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

use App\CoreModule\Exceptions\InvalidPasswordException;
use App\CoreModule\Exceptions\UsernameAlreadyExistsException;
use App\Models\Database\Entities\User;
use App\Models\Database\EntityManager;
use Nette\SmartObject;

/**
 * Tool for managing users
 */
class UserManager {

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
	 * Changes the user's password
	 * @param int $id User ID
	 * @param string $oldPassword Old password
	 * @param string $newPassword New password
	 * @throws InvalidPasswordException
	 */
	public function changePassword(int $id, string $oldPassword, string $newPassword): void {
		$user = $this->entityManager->getUserRepository()->find($id);
		if (!$user->verifyPassword($oldPassword)) {
			throw new InvalidPasswordException();
		}
		$this->editPassword($id, $newPassword);
	}

	/**
	 * Edits rhe user's password
	 * @param int $id User ID
	 * @param string $password New User's password
	 */
	public function editPassword(int $id, string $password): void {
		$user = $this->entityManager->getUserRepository()->find($id);
		$user->setPassword($password);
		$this->entityManager->persist($user);
		$this->entityManager->flush();
	}

	/**
	 * Deletes the user
	 * @param int $id User ID
	 */
	public function delete(int $id): void {
		$user = $this->entityManager->getUserRepository()->find($id);
		$this->entityManager->remove($user);
		$this->entityManager->flush();
	}

	/**
	 * Edits the user
	 * @param int $id User ID
	 * @param string|null $username New username
	 * @param string|null $role New user role
	 * @param string|null $language New user's language
	 * @throws UsernameAlreadyExistsException
	 */
	public function edit(int $id, ?string $username, ?string $role, ?string $language): void {
		$userRepository = $this->entityManager->getUserRepository();
		$user = $userRepository->find($id);
		if ($username !== null) {
			$userWithName = $userRepository->findOneByUserName($username);
			if ($userWithName !== null && $userWithName->getId() !== $id) {
				throw new UsernameAlreadyExistsException();
			}
			$user->setUserName($username);
		}
		if ($role !== null) {
			$user->setRole($role);
		}
		if ($language !== null) {
			$user->setLanguage($language);
		}
		$this->entityManager->persist($user);
		$this->entityManager->flush();
	}

	/**
	 * Gets information about the user
	 * @param int $id User ID
	 * @return mixed[]|null Information about the user or null
	 */
	public function getInfo(int $id): ?array {
		$user = $this->entityManager->getUserRepository()->find($id);
		if ($user === null) {
			return null;
		}
		return $user->toArray();
	}

	/**
	 * Gets all registered users
	 * @return mixed[] Registered users
	 */
	public function getUsers(): array {
		$users = [];
		foreach ($this->entityManager->getUserRepository()->findAll() as $user) {
			$array = $user->toArray();
			unset($array['password']);
			$users[] = $array;
		}
		return $users;
	}

	/**
	 * Registers a new user
	 * @param string $username Username
	 * @param string $password Password
	 * @param string $role User's role
	 * @param string $language User's language
	 * @throws UsernameAlreadyExistsException
	 */
	public function register(string $username, string $password, string $role, string $language): void {
		$user = $this->entityManager->getUserRepository()->findOneByUserName($username);
		$hash = password_hash($password, PASSWORD_DEFAULT);
		if ($user !== null) {
			throw new UsernameAlreadyExistsException();
		}
		$user = new User($username, $hash, $role, $language);
		$this->entityManager->persist($user);
		$this->entityManager->flush();
	}

}
