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

namespace App\ApiModule\Version0\Controllers\Config;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\ApiModule\Version0\RequestAttributes;
use App\Entities\MailerConfiguration;
use App\Exceptions\InvalidSmtpConfigException;
use App\Models\Mail\ConfigurationManager;
use App\Models\Mail\Senders\MailerConfigurationTestMailSender;
use Nette\IOException;
use Nette\Mail\SendException;
use Nette\Utils\JsonException;

/**
 * Mailer configuration controller
 */
#[Path('/mailer')]
#[Tag('Configuration - Mailer')]
class MailerController extends BaseConfigController {

	/**
	 * Constructor
	 * @param ConfigurationManager $manager Mailer configuration manager
	 * @param MailerConfigurationTestMailSender $sender Mailer configuration test mail sender
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly ConfigurationManager $manager,
		private readonly MailerConfigurationTestMailSender $sender,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns the current configuration of the mailer
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/MailerConfiguration'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function getConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['mailer']);
		try {
			$config = $this->manager->read();
			$response = $response->writeJsonObject($config);
			return $this->validators->validateResponse('mailer', $response);
		} catch (JsonException $e) {
			throw new ServerErrorException('Invalid JSON syntax', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Updates the configuration of the mailer
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/MailerConfiguration'
		responses:
			'200':
				description: Success
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function setConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['mailer']);
		$this->validators->validateRequest('mailer', $request);
		try {
			$configuration = MailerConfiguration::jsonDeserialize($request->getJsonBody());
			$this->manager->test($configuration);
			$this->manager->write($configuration);
			$user = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
			if ($user->getEmail() !== null) {
				$this->sender->send($user);
			}
			return $response;
		} catch (IOException | InvalidSmtpConfigException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (SendException $e) {
			throw new ServerErrorException('Configuration saved successfully, but unable to send test email.', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/test')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Sends a test e-mail to verify configuration of the mailer
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/MailerConfiguration'
		responses:
			'200':
				description: Success
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				description: Unable to send the e-mail
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Error'
	EOT)]
	public function testConfiguration(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['mailer']);
		$this->validators->validateRequest('mailer', $request);
		$user = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
		try {
			$configuration = $request->getJsonBodyCopy();
			$this->manager->test(MailerConfiguration::jsonDeserialize($configuration));
			$this->sender->send($user, $configuration);
		} catch (IOException | InvalidSmtpConfigException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (SendException $e) {
			throw new ServerErrorException('Unable to send the e-mail', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
		return $response;
	}

}
