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
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\CoreModule\Exceptions\Users\UserRoleInvalidException;
use App\CoreModule\Models\UserManager;
use App\Exceptions\InvalidEmailAddressException;
use App\GatewayModule\Models\InfoManager;
use App\InstallModule\Exceptions\FactoryPasswordNotConfiguredException;
use App\InstallModule\Models\DependencyManager;
use App\InstallModule\Models\InstallWizardManager;
use App\InstallModule\Models\PhpModuleManager;
use App\InstallModule\Models\SudoManager;
use App\Models\Database\EntityManager;
use Doctrine\Migrations\DependencyFactory as MigrationsDependencyFactory;
use Nette\Mail\SendException;
use Nette\Utils\Strings;

/**
 * Installation controller
 */
#[Path('/installation')]
#[Tag('Installation manager')]
class InstallationController extends BaseController {

	/**
	 * Constructor
	 * @param DependencyManager $dependencyManager Dependency manager
	 * @param EntityManager $entityManager Database entity manager
	 * @param MigrationsDependencyFactory $migrationsDependencyFactory Doctrine migrations dependency factory
	 * @param PhpModuleManager $phpModuleManager Php module manager
	 * @param SudoManager $sudoManager Sudo manager
	 * @param InfoManager $infoManager Info manager
	 * @param InstallWizardManager $installWizardManager Install wizard manager
	 * @param UserManager $userManager User manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly DependencyManager $dependencyManager,
		private readonly EntityManager $entityManager,
		private readonly MigrationsDependencyFactory $migrationsDependencyFactory,
		private readonly PhpModuleManager $phpModuleManager,
		private readonly SudoManager $sudoManager,
		private readonly InfoManager $infoManager,
		private readonly InstallWizardManager $installWizardManager,
		private readonly UserManager $userManager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Checks the installation
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/InstallationCheck'
	EOT)]
	public function check(ApiRequest $request, ApiResponse $response): ApiResponse {
		$gwId = $this->infoManager->getId();
		$status = [
			'gwId' => $gwId === null ? null : Strings::lower($gwId),
		];
		$status['allMigrationsExecuted'] = $this->migrationsDependencyFactory->getMigrationStatusCalculator()->getNewMigrations()->count() === 0;
		$status['phpModules'] = $this->phpModuleManager::checkModules();
		$sudo = $this->sudoManager->checkSudo();
		if ($sudo !== []) {
			$status['sudo'] = $sudo;
		}
		$status['dependencies'] = $this->dependencyManager->listMissing();
		if (!$status['allMigrationsExecuted']) {
			$response = $response->writeJsonBody($status);
			return $this->validators->validateResponse('installationCheck', $response);
		}
		$users = $this->entityManager->getUserRepository()->count([]);
		$status['hasUsers'] = $users !== 0;
		$response = $response->writeJsonBody($status);
		return $this->validators->validateResponse('installationCheck', $response);
	}

	#[Path('/user')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Creates initial user for installation wizard
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/InstallUserCreate'
		responses:
			'201':
				description: Created
				headers:
					Location:
						description: Location of information about the created user
						schema:
							type: string
			'400':
				$ref: '#/components/responses/BadRequest'
			'401':
				description: Unathorized install wizard access
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				description: Unable to verify access
	EOT)]
	public function createUser(ApiRequest $request, ApiResponse $response): ApiResponse {
		if ($this->userManager->hasUsers()) {
			throw new ClientErrorException('Initial user already exists', ApiResponse::S403_FORBIDDEN);
		}
		$this->validators->validateRequest('installUserCreate', $request);
		$json = $request->getJsonBodyCopy();
		try {
			if (!$this->installWizardManager->verifyAccess($json['factoryPassword'])) {
				throw new ClientErrorException('Invalid factory password', ApiResponse::S401_UNAUTHORIZED);
			}
			unset($json['factoryPassword']);
			$user = $this->userManager->create($json);
		} catch (InvalidEmailAddressException $e) {
			throw new ClientErrorException('Invalid email address: ' . $e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		} catch (UserRoleInvalidException $e) {
			throw new ClientErrorException('Invalid role', ApiResponse::S400_BAD_REQUEST, $e);
		} catch (FactoryPasswordNotConfiguredException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
		$responseBody = ['emailSent' => false];
		try {
			$this->userManager->sendVerificationEmail($user, $this->getBaseUrl($request));
			$responseBody['emailSent'] = true;
		} catch (SendException) {
			// Ignore failure
		}
		return $response->withStatus(ApiResponse::S201_CREATED)
			->withHeader('Location', '/api/v0/users/' . $user->getId())
			->writeJsonBody($responseBody);
	}

}
