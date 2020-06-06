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
use Apitte\Core\Annotation\Controller\Response;
use Apitte\Core\Annotation\Controller\Responses;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\CoreModule\Exceptions\NonexistentUserException;
use App\CoreModule\Exceptions\UsernameAlreadyExistsException;
use App\CoreModule\Models\UserManager;
use App\Exceptions\InvalidUserLanguageException;
use App\Exceptions\InvalidUserRoleException;
use Nette\Utils\JsonException;

/**
 * User manager API controller
 * @Path("/users")
 * @Tag("User manager")
 */
class UsersController extends BaseController {

	/**
	 * @var UserManager
	 */
	private $userManager;

	/**
	 * Constructor
	 * @param UserManager $userManager User manager
	 */
	public function __construct(UserManager $userManager) {
		$this->userManager = $userManager;
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
		$users = $this->userManager->getUsers();
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
	 * ")
	 * @Responses({
	 *      @Response(code="201", description="Created"),
	 *      @Response(code="400", description="Bad Request")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function create(ApiRequest $request, ApiResponse $response): ApiResponse {
		$json = $request->getJsonBody();
		if (!array_key_exists('username', $json)) {
			return $response->withStatus(400, 'Missing username');
		}
		if (!array_key_exists('password', $json)) {
			return $response->withStatus(400, 'Missing password');
		}
		if (!array_key_exists('role', $json)) {
			return $response->withStatus(400, 'Missing role');
		}
		if (!array_key_exists('language', $json)) {
			return $response->withStatus(400, 'Missing language');
		}
		try {
			$this->userManager->register($json['username'], $json['password'], $json['role'], $json['language']);
		} catch (UsernameAlreadyExistsException $e) {
			return $response->withStatus(400, 'Username already exists');
		} catch (InvalidUserLanguageException $e) {
			return $response->withStatus(400, 'Invalid user language');
		} catch (InvalidUserRoleException $e) {
			return $response->withStatus(400, 'Invalid user role');
		}
		return $response->withStatus(201);
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
		$user = $this->userManager->getInfo((int) $request->getParameter('id'));
		if ($user === null) {
			return $response->withStatus(404);
		}
		unset($user['password']);
		return $response->writeJsonBody($user);
	}

	/**
	 * @Path("/{id}")
	 * @Method("DELETE")
	 * @OpenApi("
	 *   summary: Deletes a user
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="User ID")
	 * })
	 * @Responses({
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="404", description="Not found")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function delete(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$this->userManager->delete((int) $request->getParameter('id'));
			return $response->withStatus(200);
		} catch (NonexistentUserException $e) {
			return $response->withStatus(404, 'User not found');
		}
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
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="User ID")
	 * })
	 * @Responses({
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="400", description="Bad request"),
	 *      @Response(code="404", description="Not found")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		$id = (int) $request->getParameter('id');
		try {
			$json = $request->getJsonBody();
		} catch (JsonException $e) {
			return $response->withStatus(400, 'Invalid JSON syntax');
		}
		try {
			$this->userManager->edit($id, $json['username'] ?? null, $json['role'] ?? null, $json['language'] ?? null);
			return $response->withStatus(200);
		} catch (UsernameAlreadyExistsException $e) {
			return $response->withStatus(400, 'Username already exists.');
		} catch (NonexistentUserException $e) {
			return $response->withStatus(404, 'User not found.');
		} catch (InvalidUserLanguageException $e) {
			return $response->withStatus(400, 'Invalid user language');
		} catch (InvalidUserRoleException $e) {
			return $response->withStatus(400, 'Invalid user role');
		}
	}

}
