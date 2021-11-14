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
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\JwtConfigurator;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\ApiModule\Version0\RequestAttributes;
use App\Models\Database\Entities\User;
use App\Models\Database\EntityManager;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Throwable;
use function gethostname;

/**
 * User manager API controller
 * @Path("/user")
 * @Tag("User manager")
 */
class UserController extends BaseController {

	/**
	 * @var Configuration JWT configuration
	 */
	private $configuration;

	/**
	 * @var EntityManager Entity manager
	 */
	private $entityManager;

	/**
	 * Constructor
	 * @param JwtConfigurator $configurator JWT configurator
	 * @param EntityManager $entityManager Entity manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(JwtConfigurator $configurator, EntityManager $entityManager, RestApiSchemaValidator $validator) {
		$this->configuration = $configurator->create();
		$this->entityManager = $entityManager;
		parent::__construct($validator);
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns information about logged in user
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/UserDetail'
	 *      '403':
	 *          description: Forbidden - API key is used
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		$user = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
		if ($user instanceof User) {
			return $response->writeJsonObject($user);
		}
		throw new ClientErrorException('API key is used.', ApiResponse::S403_FORBIDDEN);
	}

	/**
	 * @Path("/password")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Changes the password
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/PasswordChange'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '403':
	 *          description: Forbidden - API key is used
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function changePassword(ApiRequest $request, ApiResponse $response): ApiResponse {
		$user = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
		if (!($user instanceof User)) {
			throw new ClientErrorException('API key is used.', ApiResponse::S403_FORBIDDEN);
		}
		$this->validator->validateRequest('passwordChange', $request);
		$body = $request->getJsonBody();
		if (!$user->verifyPassword($body['old'])) {
			throw new ClientErrorException('Old password is incorrect', ApiResponse::S400_BAD_REQUEST);
		}
		$user->setPassword($body['new']);
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		return $response->writeBody('Workaround');
	}

	/**
	 * @Path("/signIn")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Signs in the user
	 *  security:
	 *     - []
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/UserSignIn'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/UserToken'
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function signIn(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validator->validateRequest('userSignIn', $request);
		$credentials = $request->getJsonBody();
		$user = $this->entityManager->getUserRepository()->findOneByUserName($credentials['username']);
		if (!($user instanceof User)) {
			throw new ClientErrorException('Invalid credentials', ApiResponse::S400_BAD_REQUEST);
		}
		if (!$user->verifyPassword($credentials['password'])) {
			throw new ClientErrorException('Invalid credentials', ApiResponse::S400_BAD_REQUEST);
		}
		try {
			$now = new DateTimeImmutable();
			$us = $now->format('u');
			$now = $now->modify('-' . $us . ' usec');
		} catch (Throwable $e) {
			throw new ServerErrorException('Date creation error', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
		$hostname = gethostname();
		$builder = $this->configuration->createBuilder()
			->issuedAt($now)
			->expiresAt($now->modify('+90 min'))
			->withClaim('uid', $user->getId());
		if ($hostname !== false) {
			$builder->issuedBy($hostname)->identifiedBy($hostname);
		}
		$signer = $this->configuration->getSigner();
		$signingKey = $this->configuration->getSigningKey();
		$token = $builder->getToken($signer, $signingKey);
		$json = $user->jsonSerialize();
		$json['token'] = (string) $token;
		return $response->writeJsonBody($json);
	}

}
