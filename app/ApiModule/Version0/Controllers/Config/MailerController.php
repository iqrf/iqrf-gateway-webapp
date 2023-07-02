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

namespace App\ApiModule\Version0\Controllers\Config;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\BaseConfigController;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\ApiModule\Version0\RequestAttributes;
use App\Exceptions\InvalidSmtpConfigException;
use App\Models\Mail\ConfigurationManager;
use App\Models\Mail\Senders\MailerConfigurationTestMailSender;
use Nette\IOException;
use Nette\Mail\SendException;
use Nette\Utils\JsonException;

/**
 * Mailer configuration controller
 * @Path("/mailer")
 * @Tag("Mailer configuration")
 */
class MailerController extends BaseConfigController {

	/**
	 * @var ConfigurationManager Mailer configuration manager
	 */
	private ConfigurationManager $manager;

	/**
	 * @var MailerConfigurationTestMailSender Mailer configuration test mail sender
	 */
	private MailerConfigurationTestMailSender $configurationTestSender;

	/**
	 * Constructor
	 * @param ConfigurationManager $manager Mailer configuration manager
	 * @param MailerConfigurationTestMailSender $sender Mailer configuration test mail sender
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(ConfigurationManager $manager, MailerConfigurationTestMailSender $sender, RestApiSchemaValidator $validator) {
		$this->manager = $manager;
		$this->configurationTestSender = $sender;
		parent::__construct($validator);
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns current configuration of the mailer
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/MailerConfiguration'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function getConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['mailer']);
		try {
			$config = $this->manager->read();
			return $response->writeJsonBody($config);
		} catch (JsonException $e) {
			throw new ServerErrorException('Invalid JSON syntax', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Saves new configuration of the mailer
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/MailerConfiguration'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function setConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['mailer']);
		$this->validator->validateRequest('mailer', $request);
		try {
			$configuration = $request->getJsonBody();
			$this->manager->test($configuration);
			$this->manager->write($configuration);
			$user = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
			if ($user->getEmail() !== null) {
				$this->configurationTestSender->send($user);
			}
			return $response->writeBody('Workaround');
		} catch (IOException | InvalidSmtpConfigException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (SendException $e) {
			throw new ServerErrorException('Configuration saved successfully, but unable to send test email.', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/test")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Sends a test e-mail to verify configuration of the mailer
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/MailerConfiguration'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '500':
	 *          description: Unable to send the e-mail
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function testConfiguration(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['mailer']);
		$this->validator->validateRequest('mailer', $request);
		$user = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
		try {
			$this->configurationTestSender->send($user, $request->getJsonBody());
		} catch (SendException $e) {
			throw new ServerErrorException('Unable to send the e-mail', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
		return $response->writeBody('Workaround');
	}

}
