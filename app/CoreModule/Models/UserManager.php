<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

use Apitte\Core\Http\ApiRequest;
use App\Models\Database\Entities\User;
use App\Models\Database\Entities\UserVerification;
use App\Models\Database\EntityManager;
use App\Models\Database\Enums\UserRole;
use App\Models\Database\Repositories\UserRepository;
use App\Models\Mail\Senders\EmailVerificationMailSender;
use App\Models\Mail\Senders\PasswordChangeConfirmationMailSender;
use Nette\Mail\SendException;

/**
 * User manager
 */
class UserManager {

	/**
	 * @var UserRepository User database repository
	 */
	private readonly UserRepository $repository;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 * @param EmailVerificationMailSender $emailVerificationSender Email verification sender
	 * @param PasswordChangeConfirmationMailSender $passwordChangeConfirmationSender Password change confirmation sender
	 */
	public function __construct(
		private readonly EntityManager $entityManager,
		private readonly EmailVerificationMailSender $emailVerificationSender,
		private readonly PasswordChangeConfirmationMailSender $passwordChangeConfirmationSender,
	) {
		$this->repository = $entityManager->getUserRepository();
	}

	/**
	 * Checks the e-mail address uniqueness
	 * @param string $email E-mail address
	 * @param int|null $userId User ID
	 * @return bool E-mail address uniqueness
	 */
	public function checkEmailUniqueness(string $email, ?int $userId = null): bool {
		$user = $this->entityManager->getUserRepository()->findOneByEmail($email);
		return $user instanceof User && $user->getId() !== $userId;
	}

	/**
	 * Checks the username uniqueness
	 * @param string $username Username
	 * @param int|null $userId User ID
	 * @return bool Username uniqueness
	 */
	public function checkUsernameUniqueness(string $username, ?int $userId = null): bool {
		$user = $this->entityManager->getUserRepository()->findOneByUserName($username);
		return $user instanceof User && $user->getId() !== $userId;
	}

	/**
	 * Lists all users
	 * @param array<UserRole> $roles User roles to filter
	 * @return array<User> Users
	 */
	public function list(array $roles = []): array {
		$criteria = $roles === [] ? [] : ['role' => array_map(static fn (UserRole $role): string => $role->value, $roles)];
		return $this->repository->findBy($criteria);
	}

	/**
	 * Sends user verification e-mail
	 * @param ApiRequest $request API request
	 * @param User $user User
	 * @throws SendException
	 */
	public function sendVerificationEmail(ApiRequest $request, User $user): void {
		$body = $request->getJsonBodyCopy();
		if (array_key_exists('baseUrl', $body)) {
			$baseUrl = trim($body['baseUrl'], '/');
		} else {
			$baseUrl = explode('/api/v0/', (string) $request->getUri(), 2)[0];
		}
		if ($user->verification instanceof UserVerification) {
			$this->entityManager->remove($user->verification);
			$this->entityManager->flush();
		}
		$user->verification = new UserVerification($user);
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		$this->emailVerificationSender->send($user->verification, $baseUrl);
	}

	/**
	 * Send password change confirmation e-mail
	 * @param ApiRequest $request API request
	 * @param User $user User
	 * @throws SendException
	 */
	public function sendPasswordChangeConfirmationEmail(ApiRequest $request, User $user): void {
		$body = $request->getJsonBodyCopy();
		if (array_key_exists('baseUrl', $body)) {
			$baseUrl = trim($body['baseUrl'], '/');
		} else {
			$baseUrl = explode('/api/v0/', (string) $request->getUri(), 2)[0];
		}
		$this->passwordChangeConfirmationSender->send($user, $baseUrl);
	}

}
