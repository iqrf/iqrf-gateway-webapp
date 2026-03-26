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

use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Http\ApiResponse;
use App\Exceptions\InvalidUserStateException;
use App\Exceptions\ResourceNotFoundException;
use App\Models\Database\Entities\PasswordRecovery;
use App\Models\Database\Entities\User;
use App\Models\Database\Entities\UserInvitation;
use App\Models\Database\Entities\UserVerification;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\UserRepository;
use App\Models\Mail\Senders\UserMailSender;
use BadMethodCallException;
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
	 * @param UserMailSender $mailSender User e-mail sender
	 */
	public function __construct(
		private readonly EntityManager $entityManager,
		private readonly UserMailSender $mailSender,
	) {
		$this->repository = $entityManager->getUserRepository();
	}

	/**
	 * Blocks user
	 * @param User $user User to block
	 * @throws InvalidUserStateException User is already blocked
	 */
	public function block(User $user): void {
		$user->setState($user->getState()->block());
		$this->entityManager->persist($user);
		$this->entityManager->flush();
	}

	/**
	 * Unblocks user
	 * @param User $user User to unblock
	 * @throws InvalidUserStateException User is already unblocked
	 */
	public function unblock(User $user): void {
		$user->setState($user->getState()->unblock());
		$this->entityManager->persist($user);
		$this->entityManager->flush();
	}

	/**
	 * Checks the e-mail address uniqueness
	 * @param string $email E-mail address
	 * @param int|null $userId User ID
	 * @return bool E-mail address uniqueness
	 */
	public function checkEmailUniqueness(string $email, ?int $userId = null): bool {
		$user = $this->repository->findOneByEmail($email);
		return $user instanceof User && $user->getId() !== $userId;
	}

	/**
	 * Checks the username uniqueness
	 * @param string $username Username
	 * @param int|null $userId User ID
	 * @return bool Username uniqueness
	 */
	public function checkUsernameUniqueness(string $username, ?int $userId = null): bool {
		$user = $this->repository->findOneByUserName($username);
		return $user instanceof User && $user->getId() !== $userId;
	}

	/**
	 * Create password recovery request
	 * @param User $user User
	 * @param string $baseUrl Frontend base URL
	 * @throws BadMethodCallException User's e-mail address is not verified.
	 * @throws SendException Failed to send e-mail message
	 */
	public function createPasswordRecoveryRequest(User $user, string $baseUrl): void {
		if ($user->getEmail() === null) {
			throw new ClientErrorException('User does not have an e-mail address', ApiResponse::S400_BAD_REQUEST);
		}
		if ($user->getState()->isBlocked()) {
			throw new BadMethodCallException('User is blocked');
		}
		if (!$user->getState()->isVerified()) {
			throw new BadMethodCallException('E-mail address is not verified');
		}
		if ($user->passwordRecovery !== null) {
			$this->entityManager->remove($user->passwordRecovery);
			$this->entityManager->flush();
		}
		$user->passwordRecovery = new PasswordRecovery($user);
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		assert($user->passwordRecovery instanceof PasswordRecovery);
		$this->mailSender->sendPasswordRecovery($user->passwordRecovery, $baseUrl);
	}

	/**
	 * Returns the user by e-mail address
	 * @param string $email User e-mail address
	 * @return User User entity
	 * @throws ResourceNotFoundException User with the e-mail address not found
	 */
	public function getByEmail(string $email): User {
		$user = $this->repository->findOneByEmail($email);
		if ($user instanceof User) {
			return $user;
		}
		throw new ResourceNotFoundException('User with e-mail address ' . $email . ' not found');
	}

	/**
	 * Returns the user by user name
	 * @param string $username User name
	 * @return User User entity
	 * @throws ResourceNotFoundException User with the user name not found
	 */
	public function getByUserName(string $username): User {
		$user = $this->repository->findOneByUserName($username);
		if ($user instanceof User) {
			return $user;
		}
		throw new ResourceNotFoundException('User with username ' . $username . ' not found');
	}

	/**
	 * Returns the user by ID
	 * @param int $id User ID
	 * @return User User entity
	 * @throws ResourceNotFoundException User with the ID not found
	 */
	public function get(int $id): User {
		$user = $this->repository->find($id);
		if ($user instanceof User) {
			return $user;
		}
		throw new ResourceNotFoundException('User with ID ' . $id . ' not found');
	}

	/**
	 * Lists all users
	 * @param array<Role> $roles User roles to filter
	 * @return array<User> Users
	 */
	public function list(array $roles = []): array {
		$criteria = $roles === [] ? [] : ['role' => $roles];
		return $this->repository->findBy($criteria);
	}

	/**
	 * Sends user invitation e-mail
	 * @param User $user User
	 * @param string $baseUrl Frontend base URL
	 * @throws SendException
	 */
	public function sendInvitationEmail(User $user, string $baseUrl): void {
		if ($user->invitation !== null) {
			$this->entityManager->remove($user->invitation);
			$this->entityManager->flush();
		}
		$user->invitation = new UserInvitation($user);
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		assert($user->invitation !== null);
		$this->mailSender->sendPasswordSet($user->invitation, $baseUrl);
	}

	/**
	 * Sends user verification e-mail
	 * @param User $user User
	 * @param string $baseUrl REAT API base URL
	 * @throws SendException
	 */
	public function sendVerificationEmail(User $user, string $baseUrl): void {
		if ($user->verification instanceof UserVerification) {
			$this->entityManager->remove($user->verification);
			$this->entityManager->flush();
		}
		$user->verification = new UserVerification($user);
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		$this->mailSender->sendVerification($user->verification, $baseUrl);
	}

}
