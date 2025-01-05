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
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\InstallModule\Models\DependencyManager;
use App\InstallModule\Models\PhpModuleManager;
use App\InstallModule\Models\SudoManager;
use App\Models\Database\EntityManager;
use Doctrine\Migrations\DependencyFactory as MigrationsDependencyFactory;

/**
 * Installation controller
 * @Path("/installation")
 * @Tag("Installation manager")
 */
class InstallationController extends BaseController {

	/**
	 * @var DependencyManager Dependency manager
	 */
	private DependencyManager $dependencyManager;

	/**
	 * @var EntityManager Entity manager
	 */
	private EntityManager $entityManager;

	/**
	 * @var MigrationsDependencyFactory Doctrine migrations dependency factory
	 */
	private MigrationsDependencyFactory $migrationsDependencyFactory;

	/**
	 * @var PhpModuleManager PHP module manager
	 */
	private PhpModuleManager $phpModuleManager;

	/**
	 * @var SudoManager Sudo manager
	 */
	private SudoManager $sudoManager;

	/**
	 * Constructor
	 * @param DependencyManager $dependencyManager Dependency manager
	 * @param EntityManager $entityManager Database entity manager
	 * @param MigrationsDependencyFactory $migrationsDependencyFactory Doctrine migrations dependency factory
	 * @param PhpModuleManager $phpModuleManager Php module manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 * @param SudoManager $sudoManager Sudo manager
	 */
	public function __construct(DependencyManager $dependencyManager, EntityManager $entityManager, MigrationsDependencyFactory $migrationsDependencyFactory, PhpModuleManager $phpModuleManager, RestApiSchemaValidator $validator, SudoManager $sudoManager) {
		$this->dependencyManager = $dependencyManager;
		$this->entityManager = $entityManager;
		$this->migrationsDependencyFactory = $migrationsDependencyFactory;
		$this->phpModuleManager = $phpModuleManager;
		$this->sudoManager = $sudoManager;
		parent::__construct($validator);
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Checks the installation
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/InstallationCheck'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function check(ApiRequest $request, ApiResponse $response): ApiResponse {
		$status = [];
		$status['allMigrationsExecuted'] = $this->migrationsDependencyFactory->getMigrationStatusCalculator()->getNewMigrations()->count() === 0;
		$status['phpModules'] = $this->phpModuleManager::checkModules();
		$sudo = $this->sudoManager->checkSudo();
		if ($sudo !== []) {
			$status['sudo'] = $sudo;
		}
		$status['dependencies'] = $this->dependencyManager->listMissing();
		if (!$status['allMigrationsExecuted']) {
			return $response->writeJsonBody($status);
		}
		$users = $this->entityManager->getUserRepository()->count([]);
		$status['hasUsers'] = $users !== 0;
		return $response->writeJsonBody($status);
	}

}
