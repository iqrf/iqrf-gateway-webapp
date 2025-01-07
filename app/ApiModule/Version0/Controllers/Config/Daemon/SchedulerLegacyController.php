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

namespace App\ApiModule\Version0\Controllers\Config\Daemon;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\BaseController;
use App\ApiModule\Version0\Models\ControllerValidators;

/**
 * Scheduler configuration controller
 */
#[Path('/scheduler')]
#[Tag('Configuration - IQRF Gateway Daemon')]
class SchedulerLegacyController extends BaseController {

	/**
	 * Constructor
	 * @param SchedulerController $newController New scheduler controller
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly SchedulerController $newController,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Lists tasks
		deprecated: true
		description: "Deprecated in favor of the new IQRF Gateway Daemon scheduler controller, use `GET` `/config/daemon/scheduler` instead. Will be removed in the version 3.1.0."
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/TaskList'
			'403':
				$ref: '#/components/responses/Forbidden'
	EOT)]
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->list($request, $response);
	}

	#[Path('/')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Creates a new task
		deprecated: true
		description: "Deprecated in favor of the new IQRF Gateway Daemon scheduler controller, use `POST` `/config/daemon/scheduler` instead. Will be removed in the version 3.1.0."
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/Task'
		responses:
			'201':
				description: Created
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'409':
				description: Task already exists
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Error'
	EOT)]
	public function create(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->create($request, $response);
	}

	#[Path('/')]
	#[Method('DELETE')]
	#[OpenApi(<<<'EOT'
		summary: Deletes all tasks
		deprecated: true
		description: "Deprecated in favor of the new IQRF Gateway Daemon scheduler controller, use `DELETE` `/config/daemon/scheduler` instead. Will be removed in the version 3.1.0."
		responses:
			'200':
				description: Success
			'403':
				$ref: '#/components/responses/Forbidden'
	EOT)]
	public function deleteAll(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->deleteAll($request, $response);
	}

	#[Path('/{taskId}')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns task configuration
		deprecated: true
		description: "Deprecated in favor of the new IQRF Gateway Daemon scheduler controller, use `GET` `/config/daemon/scheduler/{taskId}` instead. Will be removed in the version 3.1.0."
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Task'
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
	EOT)]
	#[RequestParameter(name: 'taskId', type: 'integer', description: 'Task ID')]
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->get($request, $response);
	}

	#[Path('/{taskId}')]
	#[Method('DELETE')]
	#[OpenApi(<<<'EOT'
		summary: Deletes a task
		deprecated: true
		description: "Deprecated in favor of the new IQRF Gateway Daemon scheduler controller, use `DELETE` `/config/daemon/scheduler/{taskId}` instead. Will be removed in the version 3.1.0."
		responses:
			'200':
				description: Success
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
	EOT)]
	#[RequestParameter(name: 'taskId', type: 'integer', description: 'Task ID')]
	public function delete(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->delete($request, $response);
	}

	#[Path('/{taskId}')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Updates a task
		deprecated: true
		description: "Deprecated in favor of the new IQRF Gateway Daemon scheduler controller, use `PUT` `/config/daemon/scheduler/{taskId}` instead. Will be removed in the version 3.1.0."
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/Task'
		responses:
			'200':
				description: Success
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
	EOT)]
	#[RequestParameter(name: 'taskId', type: 'integer', description: 'Task ID')]
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->edit($request, $response);
	}

	#[Path('/export')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Exports scheduler configuration
		deprecated: true
		description: "Deprecated in favor of the new IQRF Gateway Daemon scheduler controller, use `POST` `/config/daemon/scheduler/export` instead. Will be removed in the version 3.1.0."
		responses:
			'200':
				description: Success
				content:
					application/zip:
						schema:
							type: string
							format: binary
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				description: No tasks to export
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Error'
	EOT)]
	public function export(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->export($request, $response);
	}

	#[Path('/import')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Imports scheduler configuration
		deprecated: true
		description: "Deprecated in favor of the new IQRF Gateway Daemon scheduler controller, use `POST` `/config/daemon/scheduler/import` instead. Will be removed in the version 3.1.0."
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/Task'
				application/zip:
					schema:
						type: string
						format: binary
		responses:
			'200':
				description: Success
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'415':
				$ref: '#/components/responses/InvalidContentType'
	EOT)]
	public function import(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->import($request, $response);
	}

	#[Path('/messagings')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns all messagings suitable for scheduler tasks
		deprecated: true
		description: "Deprecated in favor of the new IQRF Gateway Daemon scheduler controller, use `GET` `/config/daemon/scheduler/messagings` instead. Will be removed in the version 3.1.0."
		response:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/SchedulerMessagings'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function getMessagings(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->getMessagings($request, $response);
	}

}
