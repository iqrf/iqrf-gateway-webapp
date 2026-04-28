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

namespace App\ApiModule\Version0\Controllers\Security;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\ApiModule\Version0\RequestAttributes;
use App\CoreModule\Models\UserManager;
use App\Exceptions\InvalidEmailAddressException;
use App\Exceptions\InvalidPasswordException;
use App\Exceptions\InvalidUserRoleException;
use App\Exceptions\InvalidUserStateException;
use App\Exceptions\ResourceNotFoundException;
use App\Models\Database\Entities\User;
use App\Models\Database\EntityManager;
use App\Models\Database\Enums\UserLanguage;
use App\Models\Database\Enums\UserRole;
use App\Models\Database\Enums\UserState;
use App\Models\Database\Repositories\UserRepository;
use Nette\Mail\SendException;
use ValueError;

/**
 * User manager API controller
 */
#[Path('/users')]
#[Tag('Security - User management')]
class UsersController extends BaseSecurityController {

	/**
	 * @var UserRepository User database repository
	 */
	private readonly UserRepository $repository;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 * @param UserManager $manager User manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly EntityManager $entityManager,
		private readonly UserManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
		$this->repository = $entityManager->getUserRepository();
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Lists all users
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/UserList'
			'403':
				$ref: '#/components/responses/Forbidden'
	EOT)]
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['users:admin']);
		$response = $response->writeJsonBody($this->manager->list([]));
		return $this->validators->validateResponse('userList', $response);
	}

	#[Path('/')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Creates a new user
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/UserCreate'
		responses:
			'201':
				description: Created
				headers:
					Location:
						description: Location of information about the created user
						schema:
							type: string
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'409':
				description: E-mail address or username is already used
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Error'
	EOT)]
	public function create(ApiRequest $request, ApiResponse $response): ApiResponse {
		if ($this->repository->count([]) !== 0) {
			$this->validators->checkScopes($request, ['users:admin']);
		}
		$this->validators->validateRequest('userCreate', $request);
		$json = $request->getJsonBodyCopy();
		try {
			if ($this->manager->checkUsernameUniqueness($json['username'])) {
				throw new ClientErrorException('Username is already used', ApiResponse::S409_CONFLICT);
			}
			$email = $json['email'] ?? null;
			$password = $json['password'] ?? null;
			if ($email !== null && $this->manager->checkEmailUniqueness($email)) {
				throw new ClientErrorException('E-main address is already used', ApiResponse::S409_CONFLICT);
			}
			if ($email === null && $password === null) {
				throw new ClientErrorException('Password is required if e-mail address is not provided', ApiResponse::S400_BAD_REQUEST);
			}
			$user = new User(
				username: $json['username'],
				email: $email,
				password: $password,
				role: UserRole::fromString($json['role']),
				language: UserLanguage::from($json['language']),
			);
			if ($password === null) {
				$user->setState(UserState::Invited);
			}
			$this->entityManager->persist($user);
			$this->entityManager->flush();
		} catch (InvalidEmailAddressException $e) {
			throw new ClientErrorException('Invalid email address: ' . $e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		} catch (InvalidUserRoleException $e) {
			throw new ClientErrorException('Invalid role', ApiResponse::S400_BAD_REQUEST, $e);
		}
		$responseBody = ['emailSent' => false];
		if ($user->getEmail() !== null) {
			try {
				if ($user->getState()->isInvited()) {
					$this->manager->sendInvitationEmail($user, $this->getBaseUrl($request));
				} else {
					$this->manager->sendVerificationEmail($user, $this->getBaseUrl($request));
				}
				$responseBody['emailSent'] = true;
			} catch (SendException) {
				// Ignore failure
			}
		}
		return $response->withStatus(ApiResponse::S201_CREATED)
			->withHeader('Location', '/api/v0/users/' . $user->getId())
			->writeJsonBody($responseBody);
	}

	#[Path('/{id}')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns user by ID
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/UserDetail'
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
	EOT)]
	#[RequestParameter(name: 'id', type: 'integer', description: 'User ID')]
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['users:admin']);
		$user = $this->getUser($request);
		$response = $response->writeJsonObject($user);
		return $this->validators->validateResponse('userDetail', $response);
	}

	#[Path('/{id}')]
	#[Method('DELETE')]
	#[OpenApi(<<<'EOT'
		summary: Deletes a user
		responses:
			'200':
				description: Success
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
	EOT)]
	#[RequestParameter(name: 'id', type: 'integer', description: 'User ID')]
	public function delete(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['users:admin']);
		$user = $this->getUser($request);
		$this->entityManager->remove($user);
		$this->entityManager->flush();
		return $response->withStatus(ApiResponse::S200_OK);
	}

	#[Path('/{id}')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Updates the user
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/UserEdit'
		responses:
			'200':
				description: Success
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
			'409':
				description: Username is already used
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Error'
	EOT)]
	#[RequestParameter(name: 'id', type: 'integer', description: 'User ID')]
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['users:admin']);
		$user = $this->getUser($request);
		$this->validators->validateRequest('userEdit', $request);
		$json = $request->getJsonBodyCopy();
		if (array_key_exists('username', $json)) {
			if ($this->manager->checkUsernameUniqueness($json['username'], $user->getId())) {
				throw new ClientErrorException('Username is already used', ApiResponse::S409_CONFLICT);
			}
			$user->setUserName($json['username']);
		}
		if (array_key_exists('role', $json)) {
			if ($user->getRole() === UserRole::Admin &&
				$this->repository->userCountByRole(UserRole::Admin) === 1 &&
				$json['role'] !== UserRole::Admin->value) {
				throw new ClientErrorException('Admin user role change forbidden for the only admin user', ApiResponse::S409_CONFLICT);
			}
			try {
				$user->setRole(UserRole::fromString($json['role']));
			} catch (InvalidUserRoleException $e) {
				throw new ClientErrorException('Invalid role', ApiResponse::S400_BAD_REQUEST, $e);
			}
		}
		if (array_key_exists('language', $json)) {
			try {
				$user->setLanguage(UserLanguage::from($json['language']));
			} catch (ValueError $e) {
				throw new ClientErrorException('Invalid language', ApiResponse::S400_BAD_REQUEST, $e);
			}
		}
		if (array_key_exists('password', $json)) {
			try {
				$user->setPassword($json['password']);
			} catch (InvalidPasswordException $e) {
				throw new ClientErrorException('Invalid password', ApiResponse::S400_BAD_REQUEST, $e);
			}
		}
		if (array_key_exists('email', $json)) {
			$email = $json['email'];
			if ($email !== null && $email !== '' && $this->manager->checkEmailUniqueness($email, $user->getId())) {
				throw new ClientErrorException('E-mail address is already used', ApiResponse::S409_CONFLICT);
			}
			try {
				$user->setEmail($email);
			} catch (InvalidEmailAddressException $e) {
				throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
			}
		}
		$this->entityManager->persist($user);
		if ($user->hasChangedEmail() && $user->getEmail() !== null) {
			try {
				$this->manager->sendVerificationEmail($user, $this->getBaseUrl($request));
			} catch (SendException) {
				// Ignore failure
			}
		}
		$this->entityManager->flush();
		return $response->withStatus(ApiResponse::S200_OK);
	}

	#[Path('/{id}/block')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Blocks a user
		responses:
			"200":
				description: Success
			"403":
				$ref: "#/components/responses/Forbidden"
			"404":
				description: Not found
			"409":
				description: User is already blocked
	EOT)]
	public function block(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['users:admin']);
		try {
			$user = $this->getUser($request);
			$currentUser = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
			if ($currentUser instanceof User && $currentUser->getId() === $user->getId()) {
				throw new ClientErrorException('User cannot block itself', ApiResponse::S400_BAD_REQUEST);
			}
			$this->manager->block($user);
			return $response->withStatus(ApiResponse::S200_OK);
		} catch (InvalidUserStateException $e) {
			throw new ClientErrorException('User is already blocked', ApiResponse::S409_CONFLICT, $e);
		}
	}

	#[Path('/{id}/unblock')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Unblocks a user
		responses:
			"200":
				description: Success
			"403":
				$ref: "#/components/responses/Forbidden"
			"404":
				description: Not found
			"409":
				description: User is not blocked
	EOT)]
	public function unblock(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['users:admin']);
		try {
			$this->manager->unblock($this->getUser($request));
			return $response->withStatus(ApiResponse::S200_OK);
		} catch (InvalidUserStateException $e) {
			throw new ClientErrorException('User is not blocked', ApiResponse::S409_CONFLICT, $e);
		}
	}

	#[Path('/{id}/resendVerification')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Resends the verification e-mail
		responses:
			'200':
				description: Success
			'400':
				description: User is already verified
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Error'
			'404':
				$ref: '#/components/responses/NotFound'
			'500':
				$ref: '#/components/responses/MailerError'
	EOT)]
	#[RequestParameter(name: 'id', type: 'integer', description: 'User ID')]
	public function resendVerification(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['users:admin']);
		$user = $this->getUser($request);
		if ($user->getEmail() === null) {
			throw new ClientErrorException('User does not have an e-mail address', ApiResponse::S400_BAD_REQUEST);
		}
		if ($user->getState()->isVerified()) {
			throw new ClientErrorException('User is already verified', ApiResponse::S400_BAD_REQUEST);
		}
		try {
			if ($user->getState()->isInvited()) {
				$this->manager->sendInvitationEmail($user, $this->getBaseUrl($request));
			} elseif ($user->getState()->isUnverified()) {
				$this->manager->sendVerificationEmail($user, $this->getBaseUrl($request));
			} else {
				throw new ClientErrorException('User is not in invited or unverified state', ApiResponse::S400_BAD_REQUEST);
			}
			return $response->withStatus(ApiResponse::S200_OK);
		} catch (SendException $e) {
			throw new ServerErrorException('Unable to send the e-mail', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * Returns the user from User ID request parameter
	 * @param ApiRequest $request API request
	 * @return User User
	 * @throws ClientErrorException User not found
	 */
	private function getUser(ApiRequest $request): User {
		$id = (int) $request->getParameter('id');
		try {
			return $this->manager->get($id);
		} catch (ResourceNotFoundException) {
			throw new ClientErrorException('User not found', ApiResponse::S404_NOT_FOUND);
		}
	}

}
