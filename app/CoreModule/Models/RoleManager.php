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

namespace App\CoreModule\Models;

use App\Exceptions\InvalidRoleException;
use App\Models\Database\Entities\Role;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\RoleRepository;
use App\Models\Mail\Senders\EmailVerificationMailSender;
use App\Models\Mail\Senders\PasswordChangeConfirmationMailSender;

/**
 * User manager
 */
class RoleManager {

	/**
	 * @var RoleRepository User database repository
	 */
	private readonly RoleRepository $repository;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 * @param EmailVerificationMailSender $emailVerificationSender Email verification sender
	 * @param PasswordChangeConfirmationMailSender $passwordChangeConfirmationSender Password change confirmation sender
	 */
	public function __construct(
		private readonly EntityManager $entityManager
	) {
		$this->repository = $entityManager->getRoleRepository();
	}

	/**
	 * Checks the role name uniqueness
	 * @param string $name Role name
	 * @param int|null $roleId Role ID
	 * @return bool true if role name is already used
	 */
	public function checkRoleNameConflict(string $name, ?int $roleId = null): bool {
		$role = $this->repository->findOneByName($name);
		return $role instanceof Role && $role->getId() !== $roleId;
	}

	/**
	 * Lists all roles
	 * @return array<Role> Roles
	 */
	public function list(): array {
		return $this->repository->findAll();
	}

}
