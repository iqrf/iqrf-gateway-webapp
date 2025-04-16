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
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\GatewayModule\Models\InfoManager;
use App\InstallModule\Models\DependencyManager;
use App\InstallModule\Models\PhpModuleManager;
use App\InstallModule\Models\SudoManager;
use App\Models\Database\EntityManager;
use Doctrine\Migrations\DependencyFactory as MigrationsDependencyFactory;
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
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly DependencyManager $dependencyManager,
		private readonly EntityManager $entityManager,
		private readonly MigrationsDependencyFactory $migrationsDependencyFactory,
		private readonly PhpModuleManager $phpModuleManager,
		private readonly SudoManager $sudoManager,
		private readonly InfoManager $infoManager,
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

}
