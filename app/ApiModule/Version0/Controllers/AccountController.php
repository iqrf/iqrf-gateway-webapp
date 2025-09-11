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
use App\ApiModule\Version0\Models\ControllerValidators;
use App\ApiModule\Version0\Models\JwtConfigurator;
use App\ApiModule\Version0\RequestAttributes;
use App\CoreModule\Enums\SessionExpiration;
use App\CoreModule\Models\UserManager;
use App\Exceptions\InvalidEmailAddressException;
use App\Exceptions\InvalidPasswordException;
use App\GatewayModule\Models\Utils\GatewayInfoUtil;
use App\Models\Database\Entities\PasswordRecovery;
use App\Models\Database\Entities\User;
use App\Models\Database\Entities\UserPreferences;
use App\Models\Database\Entities\UserVerification;
use App\Models\Database\EntityManager;
use App\Models\Database\Enums\ThemePreference;
use App\Models\Database\Enums\TimeFormat;
use App\Models\Database\Enums\UserLanguage;
use App\Models\Mail\Senders\PasswordRecoveryMailSender;
use DateTimeImmutable;
use DomainException;
use Nette\Mail\SendException;
use Throwable;
use ValueError;

/**
 * User account API controller
 */
#[Path('/account')]
#[Tag('Account')]
class AccountController extends BaseController {

	/**
	 * Constructor
	 * @param JwtConfigurator $jwtConfigurator JWT configurator
	 * @param EntityManager $entityManager Entity manager
	 * @param GatewayInfoUtil $gatewayInfo Gateway info
	 * @param UserManager $manager User manager
	 * @param PasswordRecoveryMailSender $passwordRecoverySender Forgotten password recovery e-mail sender
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly JwtConfigurator $jwtConfigurator,
		private readonly EntityManager $entityManager,
		private readonly GatewayInfoUtil $gatewayInfo,
		private readonly UserManager $manager,
		private readonly PasswordRecoveryMailSender $passwordRecoverySender,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns information about the user account
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/UserDetail'
			'403':
				$ref: '#/components/responses/ForbiddenApiKey'
	EOT)]
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->onlyForUsers($request);
		$user = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
		$response = $response->writeJsonObject($user);
		return $this->validators->validateResponse('userDetail', $response);
	}

	#[Path('/')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Updates the user account information
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/AccountEdit'
		responses:
			'200':
				description: Success
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/ForbiddenApiKey'
			'409':
				description: Username or e-mail address is already used
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Error'
	EOT)]
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->onlyForUsers($request);
		$this->validators->validateRequest('accountEdit', $request);
		$user = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
		$sendVerification = false;
		$json = $request->getJsonBodyCopy();
		if (array_key_exists('username', $json)) {
			if ($this->manager->checkUsernameUniqueness($json['username'], $user->getId())) {
				throw new ClientErrorException('Username is already used', ApiResponse::S409_CONFLICT);
			}
			$user->setUserName($json['username']);
		}
		if (array_key_exists('language', $json)) {
			try {
				$user->setLanguage(UserLanguage::from($json['language']));
			} catch (ValueError $e) {
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
			} catch (SendException) {
				// Ignore failure
			}
		}
		$this->entityManager->flush();
		return $response->withStatus(ApiResponse::S200_OK);
	}

	#[Path('/preferences')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns user preferences
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/UserPreferences'
			'403':
				description: Forbidden
	EOT)]
	public function getPreferences(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->onlyForUsers($request);
		$user = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
		if ($user->preferences === null) {
			$user->preferences = new UserPreferences($user);
		}
		$response = $response->writeJsonObject($user->preferences);
		return $this->validators->validateResponse('userPreferences', $response);
	}

	#[Path('/preferences')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Edit user preferences
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/UserPreferences'
		responses:
			'200':
				description: Success
			'400':
				description: Invalid time format or theme
			'403':
				description: Forbidden
	EOT)]
	public function editPreferences(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->onlyForUsers($request);
		$this->validators->validateRequest('userPreferences', $request);
		$user = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
		$json = $request->getJsonBodyCopy();
		try {
			$timeFormat = TimeFormat::fromString($json['timeFormat']);
		} catch (DomainException $e) {
			throw new ClientErrorException('Invalid time format', ApiResponse::S400_BAD_REQUEST, $e);
		}
		try {
			$theme = ThemePreference::fromString($json['theme']);
		} catch (DomainException $e) {
			throw new ClientErrorException('Invalid theme', ApiResponse::S400_BAD_REQUEST, $e);
		}
		if ($user->preferences === null) {
			$user->preferences = new UserPreferences($user, $timeFormat, $theme);
		} else {
			$user->preferences->setTimeFormat($timeFormat);
			$user->preferences->setThemePreference($theme);
		}
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		return $response->withStatus(ApiResponse::S200_OK);
	}

	#[Path('/password')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: "Updates user's password"
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/PasswordChange'
		responses:
			'200':
				description: Success
			'403':
				$ref: '#/components/responses/ForbiddenApiKey'
	EOT)]
	public function changePassword(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->onlyForUsers($request);
		$this->validators->validateRequest('passwordChange', $request);
		$user = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
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
		} catch (SendException) {
			// ignore
		}
		return $response;
	}

	#[Path('/passwordRecovery')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Requests the password recovery
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/PasswordRecoveryRequest'
		responses:
			'200':
				description: Success
			'403':
				description: E-mail address is not verified
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Error'
			'404':
				description: User not found
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Error'
			'500':
				$ref: '#/components/responses/MailerError'
	EOT)]
	public function requestPasswordRecovery(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->validateRequest('passwordRecoveryRequest', $request);
		$body = $request->getJsonBodyCopy();
		$userRepository = $this->entityManager->getUserRepository();
		if (array_key_exists('username', $body)) {
			$user = $userRepository->findOneByUserName($body['username']);
		} elseif (array_key_exists('email', $body)) {
			$user = $userRepository->findOneByEmail($body['email']);
		} else {
			throw new ClientErrorException('E-mail address or username is required', ApiResponse::S400_BAD_REQUEST);
		}
		if (!$user instanceof User) {
			throw new ClientErrorException('User not found', ApiResponse::S404_NOT_FOUND);
		}
		if (!$user->getState()->isVerified()) {
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
		return $response;
	}

	#[Path('/passwordRecovery/{uuid}')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Recovers the forgotten password
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/PasswordRecovery'
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/UserToken'
			'404':
				$ref: '#/components/responses/NotFound'
			'410':
				description: Password recovery request is expired
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Error'
	EOT)]
	#[RequestParameter(name: 'uuid', type: 'string', description: 'Password recovery request UUID')]
	public function recoverPassword(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->validateRequest('passwordRecovery', $request);
		$body = $request->getJsonBodyCopy();
		$recoveryRequest = $this->entityManager->getPasswordRecoveryRepository()->findOneByUuid($request->getParameter('uuid'));
		if (!$recoveryRequest instanceof PasswordRecovery) {
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
		} catch (SendException) {
			// ignore
		}
		$response = $response->writeJsonBody($json);
		return $this->validators->validateResponse('userToken', $response);
	}

	#[Path('/tokenRefresh')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Refreshes user access token
		responses:
			'201':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/UserToken'
			'403':
				$ref: '#/components/responses/ForbiddenApiKey'
	EOT)]
	public function refreshToken(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->onlyForUsers($request);
		$user = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
		$json = $user->jsonSerialize();
		$json['token'] = $this->createToken($user);
		$response = $response->writeJsonBody($json);
		return $this->validators->validateResponse('userToken', $response);
	}

	#[Path('/signIn')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Signs in the user
		security:
			- []
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/UserSignIn'
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/UserToken'
			'400':
				$ref: '#/components/responses/BadRequest'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function signIn(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->validateRequest('userSignIn', $request);
		$credentials = $request->getJsonBodyCopy();
		$user = $this->entityManager->getUserRepository()->findOneByUserName($credentials['username']);
		if (!$user instanceof User) {
			throw new ClientErrorException('Invalid credentials', ApiResponse::S400_BAD_REQUEST);
		}
		if (!$user->verifyPassword($credentials['password'])) {
			throw new ClientErrorException('Invalid credentials', ApiResponse::S400_BAD_REQUEST);
		}
		if (array_key_exists('expiration', $credentials)) {
			$expiration = SessionExpiration::from($credentials['expiration']);
		} else {
			$expiration = SessionExpiration::Default;
		}
		$json = $user->jsonSerialize();
		$json['token'] = $this->createToken($user, $expiration);
		$response = $response->writeJsonBody($json);
		return $this->validators->validateResponse('userToken', $response);
	}

	#[Path('/emailVerification/resend')]
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
			'500':
				$ref: '#/components/responses/MailerError'
	EOT)]
	public function resendVerification(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->onlyForUsers($request);
		$user = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
		if ($user->getState()->isVerified()) {
			throw new ClientErrorException('User is already verified', ApiResponse::S400_BAD_REQUEST);
		}
		try {
			$this->manager->sendVerificationEmail($request, $user);
		} catch (SendException $e) {
			throw new ServerErrorException('Unable to send the e-mail', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
		return $response->withStatus(ApiResponse::S200_OK);
	}

	#[Path('/emailVerification/{uuid}')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Verifies the user
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/UserToken'
			'404':
				$ref: '#/components/responses/NotFound'
	EOT)]
	#[RequestParameter(name: 'uuid', type: 'string', description: 'User verification UUID')]
	public function verify(ApiRequest $request, ApiResponse $response): ApiResponse {
		$repository = $this->entityManager->getUserVerificationRepository();
		$verification = $repository->findOneByUuid($request->getParameter('uuid'));
		if (!$verification instanceof UserVerification) {
			throw new ClientErrorException('User verification not found', ApiResponse::S404_NOT_FOUND);
		}
		$user = $verification->user;
		$state = $user->getState();
		if ($state->isVerified()) {
			throw new ClientErrorException('User is already verified', ApiResponse::S400_BAD_REQUEST);
		}
		if ($verification->isExpired()) {
			throw new ClientErrorException('Verification link expired', ApiResponse::S410_GONE);
		}
		$user->setState($state->verify());
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		$json = $user->jsonSerialize();
		$json['token'] = $this->createToken($user);
		$response = $response->writeJsonBody($json);
		return $this->validators->validateResponse('userToken', $response);
	}

	/**
	 * Creates a new JWT token
	 * @param User $user User
	 * @param SessionExpiration|null $expiration Session expiration
	 * @return string JWT token
	 */
	private function createToken(User $user, ?SessionExpiration $expiration = null): string {
		try {
			$now = new DateTimeImmutable();
			$us = $now->format('u');
			$now = $now->modify('-' . $us . ' usec');
		} catch (Throwable $e) {
			throw new ServerErrorException('Date creation error', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
		if (!$expiration instanceof SessionExpiration) {
			$expiration = SessionExpiration::Default;
		}
		$configuration = $this->jwtConfigurator->create();
		$gwid = $this->gatewayInfo->getIdNullable();
		$builder = $configuration->builder()
			->issuedAt($now)
			->canOnlyBeUsedAfter($now)
			->expiresAt($now->modify($expiration->toDateModify()))
			->withClaim('uid', $user->getId());
		if ($gwid !== null) {
			$builder = $builder->issuedBy($gwid)
				->identifiedBy($gwid);
		}
		$signer = $configuration->signer();
		$signingKey = $configuration->signingKey();
		return $builder->getToken($signer, $signingKey)->toString();
	}

}
