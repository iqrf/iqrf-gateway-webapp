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
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\JwtConfigurator;
use App\ApiModule\Version0\RequestAttributes;
use App\Models\Database\Entities\User;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Nette\Security\AuthenticationException;
use Nette\Security\User as NetteUser;
use Nette\Utils\JsonException;
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
	 * @var NetteUser User
	 */
	private $user;

	/**
	 * Constructor
	 * @param JwtConfigurator $configurator JWT configurator
	 * @param NetteUser $user User
	 */
	public function __construct(JwtConfigurator $configurator, NetteUser $user) {
		$this->configuration = $configurator->create();
		$this->user = $user;
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
	 *      '401':
	 *          description: Unauthorized
	 *      '403':
	 *          description: Forbidden (API key is used)
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
		return $response->withStatus(ApiResponse::S403_FORBIDDEN);
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
	 *          description: Bad request
	 *      '500':
	 *          description: Server error
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function signIn(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$credentials = $request->getJsonBody();
		} catch (JsonException $e) {
			return $response->withStatus(ApiResponse::S400_BAD_REQUEST, 'Invalid JSON syntax');
		}
		try {
			$this->user->login($credentials['username'], $credentials['password']);
		} catch (AuthenticationException $e) {
			return $response->withStatus(ApiResponse::S400_BAD_REQUEST, 'Invalid credentials');
		}
		try {
			$now = new DateTimeImmutable();
			$us = $now->format('u');
			$now = $now->modify('-' . $us . ' usec');
		} catch (Throwable $e) {
			return $response->withStatus(ApiResponse::S500_INTERNAL_SERVER_ERROR);
		}
		$hostname = gethostname();
		$builder = $this->configuration->createBuilder()
			->issuedAt($now)
			->expiresAt($now->modify('+1 day'))
			->withClaim('uid', $this->user->getId());
		if ($hostname !== false) {
			$builder->issuedBy($hostname)
				->identifiedBy($hostname);
		}
		$signer = $this->configuration->getSigner();
		$signingKey = $this->configuration->getSigningKey();
		$token = $builder->getToken($signer, $signingKey);
		return $response->writeJsonBody(['token' => (string) $token]);
	}

}
