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
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\CoreModule\Models\FeatureManager;
use App\InstallModule\Exceptions\ChpasswdErrorException;
use App\InstallModule\Models\RootManager;
use App\Models\Database\EntityManager;
use Nettrine\Migrations\ContainerAwareConfiguration as DoctrineConfiguration;

/**
 * Installation controller
 * @Path("/installation")
 * @Tag("Installation manager")
 */
class InstallationController extends BaseController {

	/**
	 * @var DoctrineConfiguration Doctrine configuration
	 */
	private $doctrineConfiguration;

	/**
	 * @var EntityManager Entity manager
	 */
	private $entityManager;

	/**
	 * @var FeatureManager Feature manager
	 */
	private $featureManager;

	/**
	 * @var RootManager Root manager
	 */
	private $rootManager;

	/**
	 * Constructor
	 * @param DoctrineConfiguration $doctrineConfiguration Doctrine configuration
	 * @param EntityManager $entityManager Database entity manager
	 * @param FeatureManager $featureManager Feature manager
	 * @param RootManager $rootManager Root manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(DoctrineConfiguration $doctrineConfiguration, EntityManager $entityManager, FeatureManager $featureManager, RootManager $rootManager, RestApiSchemaValidator $validator) {
		$this->doctrineConfiguration = $doctrineConfiguration;
		$this->entityManager = $entityManager;
		$this->featureManager = $featureManager;
		$this->rootManager = $rootManager;
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
		$doctrine = $this->doctrineConfiguration;
		$availableMigrations = $doctrine->getNumberOfAvailableMigrations();
		$executedMigrations = $doctrine->getNumberOfExecutedMigrations();
		$status['allMigrationsExecuted'] = $availableMigrations === $executedMigrations;
		if (!$status['allMigrationsExecuted']) {
			return $response->writeJsonBody($status);
		}
		$users = $this->entityManager->getUserRepository()->count([]);
		$status['hasUsers'] = $users !== 0;
		return $response->writeJsonBody($status);
	}

	/**
	 * @Path("/rootpass")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Sets root password
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/RootPassword'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function setRootPassword(ApiRequest $request, ApiResponse $response): ApiResponse {
		if (!$this->featureManager->isEnabled('rootpass')) {
			throw new ClientErrorException('Root password feature is not enabled', ApiResponse::S400_BAD_REQUEST);
		}
		$this->validator->validateRequest('rootPassword', $request);
		try {
			$this->rootManager->setPassword($request->getJsonBody(true)['password']);
			return $response->writeBody('Workaround');
		} catch (ChpasswdErrorException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
