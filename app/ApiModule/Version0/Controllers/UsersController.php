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

namespace App\ApiModule\Version0\Controllers;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\ApiModule\Version0\RequestAttributes;
use App\CoreModule\Models\UserManager;
use App\Exceptions\InvalidEmailAddressException;
use App\Exceptions\InvalidPasswordException;
use App\Exceptions\InvalidUserRoleException;
use App\Models\Database\Entities\User;
use App\Models\Database\EntityManager;
use App\Models\Database\Enums\UserLanguage;
use App\Models\Database\Enums\UserRole;
use App\Models\Database\Repositories\UserRepository;
use Nette\Mail\SendException;
use ValueError;

/**
 * User manager API controller
 */
#[Path('/users')]
#[Tag('User manager')]
class UsersController extends BaseController {

	/**
	 * @var UserRepository User database repository
	 */
	private readonly UserRepository $repository;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 * @param UserManager $manager User manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(
		private readonly EntityManager $entityManager,
		private readonly UserManager $manager,
		RestApiSchemaValidator $validator,
	) {
		$this->repository = $entityManager->getUserRepository();
		parent::__construct($validator);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi('
		summary: Lists all users
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							type: array
							items:
								$ref: \'#/components/schemas/UserDetail\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
	')]
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['users:admin', 'users:basic']);
		$user = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
		$roles = [];
		if ($user instanceof User && $user->hasScope('users:basic')) {
			$roles = [UserRole::Basic, UserRole::BasicAdmin];
		}
		return $response->writeJsonBody($this->manager->list($roles));
	}

	#[Path('/')]
	#[Method('POST')]
	#[OpenApi('
		summary: Creates a new user
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: \'#/components/schemas/UserCreate\'
		responses:
			\'201\':
				description: Created
				headers:
					Location:
						description: Location of information about the created user
						schema:
							type: string
			\'400\':
				$ref: \'#/components/responses/BadRequest\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'409\':
				description: E-mail address or username is already used
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/Error\'
	')]
	public function create(ApiRequest $request, ApiResponse $response): ApiResponse {
		if ($this->repository->count([]) !== 0) {
			self::checkScopes($request, ['users:admin', 'users:basic']);
		}
		$this->validator->validateRequest('userCreate', $request);
		$json = $request->getJsonBodyCopy();
		if ($this->repository->count([]) !== 0 &&
			!in_array($json['role'], [UserRole::Basic->value, UserRole::BasicAdmin->value], true)) {
			self::checkScopes($request, ['users:admin']);
		}
		try {
			if ($this->manager->checkUsernameUniqueness($json['username'])) {
				throw new ClientErrorException('Username is already used', ApiResponse::S409_CONFLICT);
			}
			$email = $json['email'] ?? null;
			if ($email !== null && $this->manager->checkEmailUniqueness($email)) {
				throw new ClientErrorException('E-main address is already used', ApiResponse::S409_CONFLICT);
			}
			$user = new User($json['username'], $email, $json['password'], UserRole::fromString($json['role']), UserLanguage::from($json['language']));
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
				$this->manager->sendVerificationEmail($request, $user);
				$responseBody['emailSent'] = true;
			} catch (SendException $e) {
				// Ignore failure
			}
		}
		return $response->withStatus(ApiResponse::S201_CREATED)
			->withHeader('Location', '/api/v0/users/' . $user->getId())
			->writeJsonBody($responseBody);
	}

	#[Path('/{id}')]
	#[Method('GET')]
	#[OpenApi('
		summary: Finds user by ID
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/UserDetail\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
	')]
	#[RequestParameter(name: 'id', type: 'integer', description: 'User ID')]
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['users:admin', 'users:basic']);
		$id = (int) $request->getParameter('id');
		$user = $this->repository->find($id);
		if ($user === null) {
			throw new ClientErrorException('User not found', ApiResponse::S404_NOT_FOUND);
		}
		return $response->writeJsonObject($user);
	}

	#[Path('/{id}')]
	#[Method('DELETE')]
	#[OpenApi('
		summary: Deletes a user
		responses:
			\'200\':
				description: Success
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
	')]
	#[RequestParameter(name: 'id', type: 'integer', description: 'User ID')]
	public function delete(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['users:admin', 'users:basic']);
		$id = (int) $request->getParameter('id');
		$user = $this->repository->find($id);
		if ($user === null) {
			throw new ClientErrorException('User not found', ApiResponse::S404_NOT_FOUND);
		}
		if (!in_array($user->getRole(), [UserRole::Basic, UserRole::BasicAdmin], true)) {
			self::checkScopes($request, ['users:admin']);
		}
		$this->entityManager->remove($user);
		$this->entityManager->flush();
		return $response->withStatus(ApiResponse::S200_OK)
			->writeBody('Workaround');
	}

	#[Path('/{id}')]
	#[Method('PUT')]
	#[OpenApi('
		summary: Edits user
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: \'#/components/schemas/UserEdit\'
		responses:
			\'200\':
				description: Success
			\'400\':
				$ref: \'#/components/responses/BadRequest\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
			\'409\':
				description: Username is already used
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/Error\'
	')]
	#[RequestParameter(name: 'id', type: 'integer', description: 'User ID')]
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['users:admin', 'users:basic']);
		$id = (int) $request->getParameter('id');
		$user = $this->repository->find($id);
		if ($user === null) {
			throw new ClientErrorException('User not found', ApiResponse::S404_NOT_FOUND);
		}
		$this->validator->validateRequest('userEdit', $request);
		$json = $request->getJsonBodyCopy();
		if (array_key_exists('username', $json)) {
			if ($this->manager->checkUsernameUniqueness($json['username'], $id)) {
				throw new ClientErrorException('Username is already used', ApiResponse::S409_CONFLICT);
			}
			$user->setUserName($json['username']);
		}
		if (array_key_exists('role', $json)) {
			if (!in_array($user->getRole(), [UserRole::Basic, UserRole::BasicAdmin], true) &&
				!in_array($json['role'], [UserRole::Basic->value, UserRole::BasicAdmin->value], true)) {
				self::checkScopes($request, ['users:admin']);
			}
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
			if ($email !== null && $email !== '' && $this->manager->checkEmailUniqueness($email, $id)) {
				throw new ClientErrorException('E-mail address is already used', ApiResponse::S409_CONFLICT);
			}
			try {
				$user->setEmail($email);
			} catch (InvalidEmailAddressException $e) {
				throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
			}
		}
		$this->entityManager->persist($user);
		if ($user->hasChangedEmail()) {
			try {
				$this->manager->sendVerificationEmail($request, $user);
			} catch (SendException $e) {
				// Ignore failure
			}
		}
		$this->entityManager->flush();
		return $response->withStatus(ApiResponse::S200_OK)
			->writeBody('Workaround');
	}

	#[Path('/{id}/resendVerification')]
	#[Method('POST')]
	#[OpenApi('
		summary: Resends the verification e-mail
		responses:
			\'200\':
				description: Success
			\'400\':
				description: User is already verified
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/Error\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
			\'500\':
				$ref: \'#/components/responses/MailerError\'
	')]
	#[RequestParameter(name: 'id', type: 'integer', description: 'User ID')]
	public function resendVerification(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['users:admin', 'users:basic']);
		$id = (int) $request->getParameter('id');
		$user = $this->repository->find($id);
		if (!($user instanceof User)) {
			throw new ClientErrorException('User not found', ApiResponse::S404_NOT_FOUND);
		}
		if ($user->getState() === User::STATE_VERIFIED) {
			throw new ClientErrorException('User is already verified', ApiResponse::S400_BAD_REQUEST);
		}
		try {
			$this->manager->sendVerificationEmail($request, $user);
		} catch (SendException $e) {
			throw new ServerErrorException('Unable to send the e-mail', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
		return $response->withStatus(ApiResponse::S200_OK)
			->writeBody('Workaround');
	}

}
