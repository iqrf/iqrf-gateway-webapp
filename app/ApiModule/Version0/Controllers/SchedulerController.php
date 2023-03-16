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

namespace App\ApiModule\Version0\Controllers;

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
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\ApiModule\Version0\Utils\ContentTypeUtil;
use App\ConfigModule\Exceptions\InvalidTaskMessageException;
use App\ConfigModule\Exceptions\TaskNotFoundException;
use App\ConfigModule\Models\SchedulerManager;
use App\ConfigModule\Models\SchedulerMigrationManager;
use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\ZipEmptyException;
use Nette\Utils\FileSystem;
use Nette\Utils\JsonException;

/**
 * Scheduler configuration controller
 */
#[Path('/scheduler')]
#[Tag('Scheduler configuration manager')]
class SchedulerController extends BaseController {

	/**
	 * Constructor
	 * @param SchedulerManager $manager IQRF Gateway Daemon's scheduler manager
	 * @param SchedulerMigrationManager $migrationManager IQRF Gateway Daemon's scheduler migration manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(
		private readonly SchedulerManager $manager,
		private readonly SchedulerMigrationManager $migrationManager,
		RestApiSchemaValidator $validator,
	) {
		parent::__construct($validator);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi('
		summary: Lists tasks
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/TaskList\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
	')]
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:daemon']);
		return $response->writeJsonBody($this->manager->list());
	}

	#[Path('/')]
	#[Method('POST')]
	#[OpenApi('
		summary: Creates a new task
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: \'#/components/schemas/Task\'
		responses:
			\'201\':
				description: Created
			\'400\':
				$ref: \'#/components/responses/BadRequest\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'409\':
				description: Task already exists
	')]
	public function create(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:daemon']);
		$this->validator->validateRequest('task', $request);
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
		return $response->withStatus(ApiResponse::S201_CREATED)
			->writeBody('Workaround');
	}

	#[Path('/')]
	#[Method('DELETE')]
	#[OpenApi('
		summary: Deletes all tasks
		responses:
			\'200\':
				description: Success
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
	')]
	public function deleteAll(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:daemon']);
		$this->manager->deleteAll();
		return $response->writeBody('Workaround');
	}

	#[Path('/{taskId}')]
	#[Method('GET')]
	#[OpenApi('
		summary: Returns task configuration
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/Task\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				description: Not found
	')]
	#[RequestParameter(name: 'taskId', type: 'integer', description: 'Task ID')]
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:daemon']);
		$taskId = $request->getParameter('taskId');
		try {
			$task = (array) $this->manager->load($taskId);
			return $response->writeJsonBody($task);
		} catch (TaskNotFoundException $e) {
			throw new ClientErrorException('Task not found', ApiResponse::S404_NOT_FOUND, $e);
		}
	}

	#[Path('/{taskId}')]
	#[Method('DELETE')]
	#[OpenApi('
		summary: Deletes a task
		responses:
			\'200\':
				description: Success
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				description: Task not found
	')]
	#[RequestParameter(name: 'taskId', type: 'integer', description: 'Task ID')]
	public function delete(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:daemon']);
		$taskId = $request->getParameter('taskId');
		try {
			$this->manager->delete($taskId);
			return $response->writeBody('Workaround');
		} catch (TaskNotFoundException $e) {
			throw new ClientErrorException('Task not found', ApiResponse::S404_NOT_FOUND, $e);
		}
	}

	#[Path('/{taskId}')]
	#[Method('PUT')]
	#[OpenApi('
		summary: Edits a task
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: \'#/components/schemas/Task\'
		responses:
			\'200\':
				description: Success
			\'400\':
				$ref: \'#/components/responses/BadRequest\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				description: Task not found
	')]
	#[RequestParameter(name: 'taskId', type: 'integer', description: 'Task ID')]
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:daemon']);
		$taskId = $request->getParameter('taskId');
		try {
			$fileName = $this->manager->getFileName($taskId);
		} catch (TaskNotFoundException $e) {
			throw new ClientErrorException('Task not found', ApiResponse::S404_NOT_FOUND, $e);
		}
		$this->validator->validateRequest('task', $request);
		$task = $request->getJsonBodyCopy(false);
		try {
			$this->manager->save($task, $fileName);
		} catch (InvalidTaskMessageException $e) {
			throw new ClientErrorException('Invalid mType', ApiResponse::S400_BAD_REQUEST, $e);
		} catch (JsonException $e) {
			throw new ServerErrorException('Invalid JSON', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
		return $response->writeBody('Workaround');
	}

	#[Path('/export')]
	#[Method('GET')]
	#[OpenApi('
		summary: Exports scheduler configuration
		responses:
			\'200\':
				description: Success
				content:
					application/zip:
						schema:
							type: string
							format: binary
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				description: No tasks to export
	')]
	public function export(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:daemon']);
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
	#[OpenApi('
		summary: Imports scheduler configuration
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: \'#/components/schemas/Task\'
				application/zip:
					schema:
						type: string
						format: binary
		responses:
			\'200\':
				description: Success
			\'400\':
				$ref: \'#/components/responses/BadRequest\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'415\':
				description: Unsupported media type
	')]
	public function import(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:daemon']);
		try {
			switch (ContentTypeUtil::getContentType($request)) {
				case 'application/zip':
				case 'application/x-zip-compressed':
					$this->importZip($request);
					break;
				case 'application/json':
					$this->importJson($request);
					break;
				default:
					throw new ClientErrorException('Unsupported media type', ApiResponse::S415_UNSUPPORTED_MEDIA_TYPE);
			}
		} catch (InvalidTaskMessageException $e) {
			throw new ClientErrorException('Invalid mType', ApiResponse::S400_BAD_REQUEST, $e);
		} catch (JsonException $e) {
			throw new ClientErrorException('Invalid JSON syntax', ApiResponse::S400_BAD_REQUEST, $e);
		} catch (InvalidJsonException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		}
		return $response->writeBody('Workaround');
	}

	/**
	 * Imports scheduler configuration from JSON file
	 * @param ApiRequest $request REST API request
	 * @throws InvalidJsonException
	 * @throws InvalidTaskMessageException
	 * @throws JsonException
	 */
	private function importJson(ApiRequest $request): void {
		$this->validator->validateRequest('task', $request);
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
