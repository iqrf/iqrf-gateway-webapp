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

namespace App\ApiModule\Version0\Controllers;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\RequestParameters;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\Exceptions\InvalidUserLanguageException;
use App\Exceptions\InvalidUserRoleException;
use App\Models\Database\Entities\User;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\UserRepository;

/**
 * User manager API controller
 * @Path("/users")
 * @Tag("User manager")
 */
class UsersController extends BaseController {

	/**
	 * @var EntityManager Entity manager
	 */
	private $entityManager;

	/**
	 * @var UserRepository User database repository
	 */
	private $repository;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(EntityManager $entityManager, RestApiSchemaValidator $validator) {
		$this->entityManager = $entityManager;
		$this->repository = $entityManager->getUserRepository();
		parent::__construct($validator);
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Lists all users
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      type: array
	 *                      items:
	 *                          $ref: '#/components/schemas/UserDetail'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		$users = $this->repository->findAll();
		return $response->writeJsonBody($users);
	}

	/**
	 * @Path("/")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Creates a new user
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/UserCreate'
	 *  responses:
	 *      '201':
	 *          description: Created
	 *          headers:
	 *              Location:
	 *                  description: Location of information about the created user
	 *                  schema:
	 *                      type: string
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '409':
	 *          description: Username is already used
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function create(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validator->validateRequest('userCreate', $request);
		$json = $request->getJsonBody();
		try {
			$user = $this->repository->findOneByUserName($json['username']);
			if ($user !== null) {
				throw new ClientErrorException('Username is already used', ApiResponse::S409_CONFLICT);
			}
			$user = new User($json['username'], $json['password'], $json['role'], $json['language']);
			$this->entityManager->persist($user);
			$this->entityManager->flush();
		} catch (InvalidUserLanguageException $e) {
			throw new ClientErrorException('Invalid language', ApiResponse::S400_BAD_REQUEST, $e);
		} catch (InvalidUserRoleException $e) {
			throw new ClientErrorException('Invalid role', ApiResponse::S400_BAD_REQUEST, $e);
		}
		return $response->withStatus(ApiResponse::S201_CREATED)
			->withHeader('Location', '/api/v0/users/' . $user->getId())
			->writeBody('Workaround');
	}

	/**
	 * @Path("/{id}")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Finds user by ID
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/UserDetail'
	 *      '404':
	 *          description: Not found
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="User ID")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		$id = (int) $request->getParameter('id');
		$user = $this->repository->find($id);
		if ($user === null) {
			throw new ClientErrorException('User not found', ApiResponse::S404_NOT_FOUND);
		}
		assert($user instanceof User);
		return $response->writeJsonObject($user);
	}

	/**
	 * @Path("/{id}")
	 * @Method("DELETE")
	 * @OpenApi("
	 *  summary: Deletes a user
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '404':
	 *          description: Not found
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="User ID")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function delete(ApiRequest $request, ApiResponse $response): ApiResponse {
		$id = (int) $request->getParameter('id');
		$user = $this->repository->find($id);
		if ($user === null) {
			throw new ClientErrorException('User not found', ApiResponse::S404_NOT_FOUND);
		}
		$this->entityManager->remove($user);
		$this->entityManager->flush();
		return $response->withStatus(ApiResponse::S200_OK)
			->writeBody('Workaround');
	}

	/**
	 * @Path("/{id}")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Edits user
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/UserEdit'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '404':
	 *          description: Not found
	 *      '409':
	 *          description: Username is already used
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="User ID")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		$id = (int) $request->getParameter('id');
		$user = $this->repository->find($id);
		if ($user === null) {
			throw new ClientErrorException('User not found', ApiResponse::S404_NOT_FOUND);
		}
		$this->validator->validateRequest('userEdit', $request);
		$json = $request->getJsonBody();
		assert($user instanceof User);
		if (array_key_exists('username', $json)) {
			$userWithName = $this->repository->findOneByUserName($json['username']);
			if ($userWithName !== null && $userWithName->getId() !== $id) {
				throw new ClientErrorException('Username is already used', ApiResponse::S409_CONFLICT);
			}
			$user->setUserName($json['username']);
		}
		if (array_key_exists('role', $json)) {
			try {
				$user->setRole($json['role']);
			} catch (InvalidUserRoleException $e) {
				throw new ClientErrorException('Invalid role', ApiResponse::S400_BAD_REQUEST, $e);
			}
		}
		if (array_key_exists('language', $json)) {
			try {
				$user->setLanguage($json['language']);
			} catch (InvalidUserLanguageException $e) {
				throw new ClientErrorException('Invalid language', ApiResponse::S400_BAD_REQUEST, $e);
			}
		}
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		return $response->withStatus(ApiResponse::S200_OK)
			->writeBody('Workaround');
	}

}
