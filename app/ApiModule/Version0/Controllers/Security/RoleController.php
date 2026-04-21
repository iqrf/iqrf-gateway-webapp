<?php

/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
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

namespace App\ApiModule\Version0\Controllers\Security;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\CoreModule\Models\RoleManager;
use App\Enums\AccessScope;
use App\Models\Database\Entities\Role;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\RoleRepository;
use DomainException;

/**
 * Role manager API controller
 */
#[Path('/roles')]
#[Tag('Security - Role management')]
class RoleController extends BaseSecurityController {

	/**
	 * @var RoleRepository Role database repository
	 */
	private readonly RoleRepository $repository;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 * @param RoleManager $manager Role manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly EntityManager $entityManager,
		private readonly RoleManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
		$this->repository = $entityManager->getRoleRepository();
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Lists all roles
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/roleList'
			'403':
				$ref: '#/components/responses/Forbidden'
	EOT)]
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, [AccessScope::security_role_read->value]);
		$response = $response->writeJsonBody($this->manager->list());
		return $this->validators->validateResponse('roleList', $response);
	}

	#[Path('/')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Creates a new role
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/roleCreate'
		responses:
			'201':
				description: Created
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/roleDetail'
				headers:
					Location:
						description: Location of information about the created role
						schema:
							type: string
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'409':
				description: Role name is already used
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Error'
	EOT)]
	public function create(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, [AccessScope::security_role_write->value]);
		$this->validators->validateRequest('roleCreate', $request);
		$json = $request->getJsonBodyCopy();
		try {
			// check name conflict
			if ($this->manager->checkRoleNameConflict($json['name'])) {
				throw new ClientErrorException('Role name is already used!', ApiResponse::S409_CONFLICT);
			}
			// create the role
			$role = new Role(
				$json['name'],
				$json['description'],
				$json['scopes']
			);
			// save role
			$this->entityManager->persist($role);
			$this->entityManager->flush();
		} catch (DomainException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		}
		$response = $response->withStatus(ApiResponse::S201_CREATED)
			->withHeader('Location', '/api/v0/roles/' . $role->getId())
			->writeJsonBody($role->jsonSerialize());
		return $this->validators->validateResponse('roleDetail', $response);
	}

	#[Path('/{id}')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns role by ID
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/roleDetail'
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
	EOT)]
	#[RequestParameter(name: 'id', type: 'integer', description: 'Role ID')]
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, [AccessScope::security_role_read->value]);
		$id = (int) $request->getParameter('id');
		$role = $this->repository->find($id);
		if ($role === null) {
			throw new ClientErrorException('Role not found', ApiResponse::S404_NOT_FOUND);
		}
		$response = $response->writeJsonObject($role);
		return $this->validators->validateResponse('roleDetail', $response);
	}

	#[Path('/{id}')]
	#[Method('DELETE')]
	#[OpenApi(<<<'EOT'
		summary: Deletes a role
		responses:
			'200':
				description: Success
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
			'409':
				$ref: '#/components/responses/Conflict'
	EOT)]
	#[RequestParameter(name: 'id', type: 'integer', description: 'Role ID')]
	public function delete(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, [AccessScope::security_role_write->value]);
		$id = (int) $request->getParameter('id');
		$role = $this->repository->find($id);
		if ($role === null) {
			throw new ClientErrorException('Role not found', ApiResponse::S404_NOT_FOUND);
		}
		if ($role->isSystem()) {
			throw new ClientErrorException('System roles can\'t be modified', ApiResponse::S403_FORBIDDEN);
		}
		$userCount = $this->entityManager->getUserRepository()->userCountByRole($role);
		$apiKeyCount = $this->entityManager->getApiKeyRepository()->apiKeyCountByRole($role);
		if ($userCount > 0 || $apiKeyCount > 0) {
			throw new ClientErrorException('Role is still assigned to users or API keys.', ApiResponse::S409_CONFLICT);
		}
		$this->entityManager->remove($role);
		$this->entityManager->flush();
		return $response->withStatus(ApiResponse::S200_OK);
	}

	#[Path('/{id}')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Updates the role
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/roleCreate'
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/roleDetail'
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
			'409':
				description: Role name is already used
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Error'
	EOT)]
	#[RequestParameter(name: 'id', type: 'integer', description: 'Role ID')]
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, [AccessScope::security_role_write->value]);
		// get role
		$id = (int) $request->getParameter('id');
		$role = $this->repository->find($id);
		if ($role === null) {
			throw new ClientErrorException('Role not found', ApiResponse::S404_NOT_FOUND);
		}
		// check if role is predefined system role
		if ($role->isSystem()) {
			throw new ClientErrorException('System roles can\'t be modified', ApiResponse::S403_FORBIDDEN);
		}
		// get new data
		$this->validators->validateRequest('roleCreate', $request);
		$json = $request->getJsonBodyCopy();
		// update role params
		if ($this->manager->checkRoleNameConflict($json['name'], $id)) {
			throw new ClientErrorException('Role name is already used', ApiResponse::S409_CONFLICT);
		}
		try {
			$role->setName($json['name']);
			$role->setDescription($json['description']);
			$role->setScopesFromStringArray($json['scopes']);
		} catch (DomainException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		}
		// save role
		$this->entityManager->persist($role);
		$this->entityManager->flush();
		$response = $response->withStatus(ApiResponse::S200_OK)->writeJsonBody($role->jsonSerialize());
		return $this->validators->validateResponse('roleDetail', $response);
	}

}
