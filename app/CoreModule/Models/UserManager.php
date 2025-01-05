<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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
use App\Models\Database\Repositories\UserRepository;
use App\Models\Mail\Senders\EmailVerificationMailSender;
use App\Models\Mail\Senders\PasswordChangeConfirmationMailSender;
use Nette\Mail\SendException;

/**
 * User manager
 */
class UserManager {

	/**
	 * @var EntityManager Entity manager
	 */
	private EntityManager $entityManager;

	/**
	 * @var UserRepository User database repository
	 */
	private UserRepository $repository;

	/**
	 * @var EmailVerificationMailSender Email verification mail sender
	 */
	private EmailVerificationMailSender $emailVerificationSender;

	/**
	 * @var PasswordChangeConfirmationMailSender Password change confirmation mail sender
	 */
	private PasswordChangeConfirmationMailSender $passwordChangeConfirmationSender;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 * @param EmailVerificationMailSender $emailVerificationSender Email verification sender
	 * @param PasswordChangeConfirmationMailSender $passwordChangeConfirmationSender Password change confirmation sender
	 */
	public function __construct(EntityManager $entityManager, EmailVerificationMailSender $emailVerificationSender, PasswordChangeConfirmationMailSender $passwordChangeConfirmationSender) {
		$this->entityManager = $entityManager;
		$this->repository = $entityManager->getUserRepository();
		$this->emailVerificationSender = $emailVerificationSender;
		$this->passwordChangeConfirmationSender = $passwordChangeConfirmationSender;
	}

	/**
	 * Checks the e-mail address uniqueness
	 * @param string $email E-mail address
	 * @param int|null $userId User ID
	 * @return bool E-mail address uniqueness
	 */
	public function checkEmailUniqueness(string $email, ?int $userId = null): bool {
		$user = $this->entityManager->getUserRepository()->findOneByEmail($email);
		return $user !== null && $user->getId() !== $userId;
	}

	/**
	 * Checks the username uniqueness
	 * @param string $username Username
	 * @param int|null $userId User ID
	 * @return bool Username uniqueness
	 */
	public function checkUsernameUniqueness(string $username, ?int $userId = null): bool {
		$user = $this->entityManager->getUserRepository()->findOneByUserName($username);
		return $user !== null && $user->getId() !== $userId;
	}

	/**
	 * Lists all users
	 * @param array<string> $roles User roles to filter
	 * @return array<User> Users
	 */
	public function list(array $roles = []): array {
		$criteria = $roles === [] ? [] : ['role' => $roles];
		return $this->repository->findBy($criteria);
	}

	/**
	 * Sends user verification e-mail
	 * @param ApiRequest $request API request
	 * @param User $user User
	 * @throws SendException
	 */
	public function sendVerificationEmail(ApiRequest $request, User $user): void {
		$user->clearVerifications();
		$verification = new UserVerification($user);
		$this->entityManager->persist($verification);
		$this->entityManager->flush();
		$body = $request->getJsonBodyCopy();
		if (array_key_exists('baseUrl', $body)) {
			$baseUrl = trim($body['baseUrl'], '/');
		} else {
			$baseUrl = explode('/api/v0/', (string) $request->getUri(), 2)[0];
		}
		$this->emailVerificationSender->send($verification, $baseUrl);
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
