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
use Apitte\Core\Annotation\Controller\RequestBody;
use Apitte\Core\Annotation\Controller\Response;
use Apitte\Core\Annotation\Controller\Responses;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\JwtConfigurator;
use App\ApiModule\Version0\RequestAttributes;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Nette\Security\AuthenticationException;
use Nette\Security\Identity;
use Nette\Security\User;

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
	 * @var User User
	 */
	private $user;

	/**
	 * Constructor
	 * @param JwtConfigurator $configurator JWT configurator
	 * @param User $user User
	 */
	public function __construct(JwtConfigurator $configurator, User $user) {
		$this->configuration = $configurator->create();
		$this->user = $user;
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *   summary: Returns information about logged in user
	 * ")
	 * @Responses({
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="401", description="Unauthorized")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		/** @var Identity $identity */
		$identity = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
		$data = $identity->getData();
		return $response->writeJsonBody([
				'id' => $identity->getId(),
				'username' => $data['username'],
				'language' => $data['language'],
				'roles' => $this->user->getRoles(),
			]);
	}

	/**
	 * @Path("/signIn")
	 * @Method("POST")
	 * @OpenApi("
	 *   summary: Signs in the user
	 *   security:
	 *     - []
	 * ")
	 * @RequestBody(entity="\App\ApiModule\Version0\Entities\Request\UserSignInEntity")
	 * @Responses({
	 *      @Response(code="200", description="Success", entity="\App\ApiModule\Version0\Entities\Response\JwtTokenEntity"),
	 *      @Response(code="400", description="Bad request")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function signIn(ApiRequest $request, ApiResponse $response): ApiResponse {
		$credentials = $request->getJsonBody();
		try {
			$this->user->login($credentials['username'], $credentials['password']);
		} catch (AuthenticationException $e) {
			return $response->withStatus(400);
		}
		$now  = new DateTimeImmutable();
		$token = $this->configuration->createBuilder()
			->issuedBy(gethostname())
			->identifiedBy(gethostname())
			->issuedAt($now)
			->expiresAt($now->modify('+1 day'))
			->withClaim('uid', $this->user->getId())
			->getToken($this->configuration->getSigner(), $this->configuration->getSigningKey());
		return $response->writeJsonBody(['token' => (string) $token]);
	}

}
