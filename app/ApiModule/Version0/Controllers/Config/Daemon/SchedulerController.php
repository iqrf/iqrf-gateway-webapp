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

use Apitte\Core\Adjuster\FileResponseAdjuster;
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
use App\ApiModule\Version0\Utils\ContentTypeUtil;
use App\ConfigModule\Exceptions\InvalidTaskMessageException;
use App\ConfigModule\Exceptions\TaskNotFoundException;
use App\ConfigModule\Models\SchedulerManager;
use App\ConfigModule\Models\SchedulerMigrationManager;
use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\ZipEmptyException;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\JsonException;

/**
 * Scheduler configuration controller
 */
#[Path('/scheduler')]
#[Tag('Configuration - IQRF Gateway Daemon')]
class SchedulerController extends BaseDaemonConfigController {

	/**
	 * Constructor
	 * @param SchedulerManager $manager IQRF Gateway Daemon's scheduler manager
	 * @param SchedulerMigrationManager $migrationManager IQRF Gateway Daemon's scheduler migration manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly SchedulerManager $manager,
		private readonly SchedulerMigrationManager $migrationManager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Lists tasks
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
		$this->validators->checkScopes($request, ['config:daemon']);
		$response = $response->writeJsonBody($this->manager->list());
		return $this->validators->validateResponse('taskList', $response);
	}

	#[Path('/')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Creates a new task
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
		$this->validators->checkScopes($request, ['config:daemon']);
		$this->validators->validateRequest('task', $request);
		$task = $request->getJsonBodyCopy(false);
		$taskId = $task->taskId;
		if ($this->manager->exist($taskId)) {
			$this->manager->getFileName($taskId);
			throw new ClientErrorException('Task already exists', ApiResponse::S409_CONFLICT);
		}
		try {
			$this->manager->save($task, null);
		} catch (InvalidTaskMessageException $e) {
			throw new ClientErrorException('Invalid mType', ApiResponse::S400_BAD_REQUEST, $e);
		} catch (JsonException $e) {
			throw new ClientErrorException('Invalid JSON syntax', ApiResponse::S400_BAD_REQUEST, $e);
		}
		return $response->withStatus(ApiResponse::S201_CREATED);
	}

	#[Path('/')]
	#[Method('DELETE')]
	#[OpenApi(<<<'EOT'
		summary: Deletes all tasks
		responses:
			'200':
				description: Success
			'403':
				$ref: '#/components/responses/Forbidden'
	EOT)]
	public function deleteAll(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['config:daemon']);
		$this->manager->deleteAll();
		return $response;
	}

	#[Path('/{taskId}')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns task configuration
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
		$this->validators->checkScopes($request, ['config:daemon']);
		$taskId = $request->getParameter('taskId');
		try {
			$task = (array) $this->manager->load($taskId);
			$response = $response->writeJsonBody($task);
			return $this->validators->validateResponse('task', $response);
		} catch (TaskNotFoundException $e) {
			throw new ClientErrorException('Task not found', ApiResponse::S404_NOT_FOUND, $e);
		}
	}

	#[Path('/{taskId}')]
	#[Method('DELETE')]
	#[OpenApi(<<<'EOT'
		summary: Deletes a task
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
		$this->validators->checkScopes($request, ['config:daemon']);
		$taskId = $request->getParameter('taskId');
		try {
			$this->manager->delete($taskId);
			return $response;
		} catch (TaskNotFoundException $e) {
			throw new ClientErrorException('Task not found', ApiResponse::S404_NOT_FOUND, $e);
		}
	}

	#[Path('/{taskId}')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Edits a task
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
		$this->validators->checkScopes($request, ['config:daemon']);
		$taskId = $request->getParameter('taskId');
		try {
			$fileName = $this->manager->getFileName($taskId);
		} catch (TaskNotFoundException $e) {
			throw new ClientErrorException('Task not found', ApiResponse::S404_NOT_FOUND, $e);
		}
		$this->validators->validateRequest('task', $request);
		$task = $request->getJsonBodyCopy(false);
		try {
			$this->manager->save($task, $fileName);
		} catch (InvalidTaskMessageException $e) {
			throw new ClientErrorException('Invalid mType', ApiResponse::S400_BAD_REQUEST, $e);
		} catch (JsonException $e) {
			throw new ServerErrorException('Invalid JSON', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
		return $response;
	}

	#[Path('/export')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Exports scheduler configuration
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
		$this->validators->checkScopes($request, ['config:daemon']);
		try {
			$path = $this->migrationManager->createArchive();
			$fileName = basename($path);
			$response->writeBody(FileSystem::read($path));
			return FileResponseAdjuster::adjust($response, $response->getBody(), $fileName, 'application/zip');
		} catch (ZipEmptyException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		}
	}

	#[Path('/import')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Imports scheduler configuration
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
		$this->validators->checkScopes($request, ['config:daemon']);
		try {
			match (ContentTypeUtil::getContentType($request)) {
				'application/zip', 'application/x-zip-compressed' => $this->importZip($request),
				'application/json' => $this->importJson($request),
				default => throw new ClientErrorException('Unsupported media type', ApiResponse::S415_UNSUPPORTED_MEDIA_TYPE),
			};
		} catch (InvalidTaskMessageException $e) {
			throw new ClientErrorException('Invalid mType', ApiResponse::S400_BAD_REQUEST, $e);
		} catch (JsonException $e) {
			throw new ClientErrorException('Invalid JSON syntax', ApiResponse::S400_BAD_REQUEST, $e);
		} catch (InvalidJsonException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		}
		return $response;
	}

	#[Path('/messagings')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns all messagings suitable for scheduler tasks
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
		$this->validators->checkScopes($request, ['config:daemon']);
		try {
			$response = $response->writeJsonBody($this->manager->getMessagings());
			return $this->validators->validateResponse('schedulerMessagings', $response);
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * Imports scheduler configuration from JSON file
	 * @param ApiRequest $request REST API request
	 * @throws InvalidJsonException
	 * @throws InvalidTaskMessageException
	 * @throws JsonException
	 */
	private function importJson(ApiRequest $request): void {
		$this->validators->validateRequest('task', $request);
		$task = $request->getJsonBodyCopy(false);
		$this->manager->save($task, null);
	}

	/**
	 * Imports scheduler configuration from ZIP archive
	 * @param ApiRequest $request REST API request
	 * @throws InvalidJsonException
	 * @throws InvalidTaskMessageException
	 * @throws JsonException
	 */
	private function importZip(ApiRequest $request): void {
		$path = '/tmp/iqrf-gateway-scheduler-upload.zip';
		FileSystem::write($path, $request->getBody()->getContents());
		$this->migrationManager->extractArchive($path);
		FileSystem::delete($path);
	}

}
