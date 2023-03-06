<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\JwtConfigurator;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\ApiModule\Version0\RequestAttributes;
use App\CoreModule\Models\UserManager;
use App\Exceptions\InvalidEmailAddressException;
use App\Exceptions\InvalidPasswordException;
use App\Exceptions\InvalidUserLanguageException;
use App\Models\Database\Entities\PasswordRecovery;
use App\Models\Database\Entities\User;
use App\Models\Database\EntityManager;
use App\Models\Mail\Senders\PasswordRecoveryMailSender;
use DateTimeImmutable;
use Nette\Mail\SendException;
use Throwable;
use function gethostname;

/**
 * User manager API controller
 * @Path("/user")
 * @Tag("User manager")
 */
class UserController extends BaseController {

	/**
	 * Constructor
	 * @param JwtConfigurator $configurator JWT configurator
	 * @param EntityManager $entityManager Entity manager
	 * @param UserManager $manager User manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 * @param PasswordRecoveryMailSender $passwordRecoverySender Forgotten password recovery e-mail sender
	 */
	public function __construct(
		private readonly JwtConfigurator $configurator,
		private readonly EntityManager $entityManager,
		private readonly UserManager $manager,
		RestApiSchemaValidator $validator,
		private readonly PasswordRecoveryMailSender $passwordRecoverySender,
	) {
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
	 * @Path("/")
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
	 *      '403':
	 *          description: Forbidden - API key is used
	 *      '409':
	 *          description: Username or e-mail address is already used
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		$user = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
		if (!($user instanceof User)) {
			throw new ClientErrorException('API key is used.', ApiResponse::S403_FORBIDDEN);
		}
		$sendVerification = false;
		$this->validator->validateRequest('userEdit', $request);
		$json = $request->getJsonBodyCopy();
		if (array_key_exists('username', $json)) {
			if ($this->manager->checkUsernameUniqueness($json['username'], $user->getId())) {
				throw new ClientErrorException('Username is already used', ApiResponse::S409_CONFLICT);
			}
			$user->setUserName($json['username']);
		}
		if (array_key_exists('language', $json)) {
			try {
				$user->setLanguage($json['language']);
			} catch (InvalidUserLanguageException $e) {
				throw new ClientErrorException('Invalid language', ApiResponse::S400_BAD_REQUEST, $e);
			}
		}
		if (array_key_exists('email', $json)) {
			$email = $json['email'];
			if ($email !== null && $email !== '') {
				if ($this->manager->checkEmailUniqueness($email, $user->getId())) {
					throw new ClientErrorException('E-main address is already used', ApiResponse::S409_CONFLICT);
				}
				if ($email !== $user->getEmail()) {
					$sendVerification = true;
				}
			}
			try {
				$user->setEmail($email);
			} catch (InvalidEmailAddressException $e) {
				throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
			}
		}
		$this->entityManager->persist($user);
		if ($sendVerification) {
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
		$body = $request->getJsonBodyCopy();
		if (!$user->verifyPassword($body['old'])) {
			throw new ClientErrorException('Old password is incorrect', ApiResponse::S400_BAD_REQUEST);
		}
		try {
			$user->setPassword($body['new']);
		} catch (InvalidPasswordException $e) {
			throw new ClientErrorException('Invalid password', ApiResponse::S400_BAD_REQUEST, $e);
		}
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		try {
			$this->manager->sendPasswordChangeConfirmationEmail($request, $user);
		} catch (SendException $e) {
			// ignore
		}
		return $response->writeBody('Workaround');
	}

	/**
	 * @Path("/password/recovery")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Requests the password recovery
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/PasswordRecoveryRequest'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '403':
	 *          description: E-mail address is not verified
	 *      '404':
	 *          description: User not found
	 *      '500':
	 *          description: Unable to send the e-mail
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function requestPasswordRecovery(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validator->validateRequest('passwordRecoveryRequest', $request);
		$body = $request->getJsonBody();
		$userRepository = $this->entityManager->getUserRepository();
		if (array_key_exists('username', $body)) {
			$user = $userRepository->findOneByUserName($body['username']);
		} elseif (array_key_exists('email', $body)) {
			$user = $userRepository->findOneByEmail($body['email']);
		} else {
			throw new ClientErrorException('E-mail address or username is required', ApiResponse::S400_BAD_REQUEST);
		}
		if ($user === null) {
			throw new ClientErrorException('User not found', ApiResponse::S404_NOT_FOUND);
		}
		if ($user->getState() !== User::STATE_VERIFIED) {
			throw new ClientErrorException('E-mail address is not verified', ApiResponse::S403_FORBIDDEN);
		}
		$recovery = new PasswordRecovery($user);
		$this->entityManager->persist($recovery);
		if (array_key_exists('baseUrl', $body)) {
			$baseUrl = trim($body['baseUrl'], '/');
		} else {
			$baseUrl = explode('/api/v0/user/password/recovery', (string) $request->getUri(), 2)[0];
		}
		try {
			$this->passwordRecoverySender->send($recovery, $baseUrl);
		} catch (SendException $e) {
			throw new ServerErrorException('Unable to send the e-mail', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
		$this->entityManager->flush();
		return $response->writeBody('Workaround');
	}

	/**
	 * @Path("/password/recovery/{uuid}")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Recovers the forgotten password
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/PasswordRecovery'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/UserToken'
	 *      '404':
	 *          description: Password recovery not found
	 *      '410':
	 *          description: Password recovery request is expired
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="uuid", type="integer", description="Password recovery request UUID")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function recoverPassword(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validator->validateRequest('passwordRecovery', $request);
		$body = $request->getJsonBodyCopy();
		$recoveryRequest = $this->entityManager->getPasswordRecoveryRepository()->findOneByUuid($request->getParameter('uuid'));
		if ($recoveryRequest === null) {
			throw new ClientErrorException('Password recovery request not found', ApiResponse::S404_NOT_FOUND);
		}
		if ($recoveryRequest->isExpired()) {
			$this->entityManager->remove($recoveryRequest);
			$this->entityManager->flush();
			throw new ClientErrorException('Password recovery request is expired', ApiResponse::S410_GONE);
		}
		$user = $recoveryRequest->getUser();
		try {
			$user->setPassword($body['password']);
		} catch (InvalidPasswordException $e) {
			throw new ClientErrorException('Invalid password', ApiResponse::S400_BAD_REQUEST, $e);
		}
		$this->entityManager->persist($user);
		$this->entityManager->remove($recoveryRequest);
		$this->entityManager->flush();
		$json = $user->jsonSerialize();
		$json['token'] = $this->createToken($user);
		try {
			$this->manager->sendPasswordChangeConfirmationEmail($request, $user);
		} catch (SendException $e) {
			// ignore
		}
		return $response->writeJsonBody($json);
	}

	/**
	 * @Path("/resendVerification")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Resends the verification e-mail
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          description: User is already verified
	 *      '500':
	 *          description: Unable to send the e-mail
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function resendVerification(ApiRequest $request, ApiResponse $response): ApiResponse {
		$user = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
		if (!($user instanceof User)) {
			throw new ClientErrorException('API key is used.', ApiResponse::S403_FORBIDDEN);
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

	/**
	 * @Path("/refreshToken")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Refreshes user access token
	 *  responses:
	 *      '201':
	 *          description: Success
	 *          content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/UserSignIn'
	 *      '403':
	 *          description: Forbidden - API key is used
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function refreshToken(ApiRequest $request, ApiResponse $response): ApiResponse {
		$user = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
		if (!($user instanceof User)) {
			throw new ClientErrorException('API key is used.', ApiResponse::S403_FORBIDDEN);
		}
		$json = $user->jsonSerialize();
		$json['token'] = $this->createToken($user);
		return $response->writeJsonBody($json);
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
		$json = $user->jsonSerialize();
		$json['token'] = $this->createToken($user);
		return $response->writeJsonBody($json);
	}

	/**
	 * @Path("/verify/{uuid}")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Verifies the user
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/UserToken'
	 *      '404':
	 *          description: Not found
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="uuid", type="integer", description="User verification UUID")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function verify(ApiRequest $request, ApiResponse $response): ApiResponse {
		$repository = $this->entityManager->getUserVerificationRepository();
		$verification = $repository->findOneByUuid($request->getParameter('uuid'));
		if ($verification === null) {
			throw new ClientErrorException('User verification not found', ApiResponse::S404_NOT_FOUND);
		}
		$user = $verification->getUser();
		$state = $user->getState();
		if ($state === User::STATE_VERIFIED) {
			throw new ClientErrorException('User is already verified', ApiResponse::S400_BAD_REQUEST);
		}
		if ($state === User::STATE_UNVERIFIED) {
			if ($verification->isExpired()) {
				throw new ClientErrorException('Verification link expired', ApiResponse::S410_GONE);
			}
			$user->setState(User::STATE_VERIFIED);
			$this->entityManager->persist($user);
		}
		$this->entityManager->flush();
		$json = $user->jsonSerialize();
		$json['token'] = $this->createToken($user);
		return $response->writeJsonBody($json);
	}

	/**
	 * Creates a new JWT token
	 * @param User $user User
	 * @return string JWT token
	 */
	private function createToken(User $user): string {
		$configuration = $this->configurator->create();
		try {
			$now = new DateTimeImmutable();
			$us = $now->format('u');
			$now = $now->modify('-' . $us . ' usec');
		} catch (Throwable $e) {
			throw new ServerErrorException('Date creation error', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
		$hostname = gethostname();
		$builder = $configuration->builder()
			->issuedAt($now)
			->expiresAt($now->modify('+90 min'))
			->withClaim('uid', $user->getId());
		if ($hostname !== false) {
			$builder->issuedBy($hostname)->identifiedBy($hostname);
		}
		$signer = $configuration->signer();
		$signingKey = $configuration->signingKey();
		return $builder->getToken($signer, $signingKey)->toString();
	}

}
