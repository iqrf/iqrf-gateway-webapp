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

use Apitte\Core\Adjuster\FileResponseAdjuster;
use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\RequestParameters;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ConfigModule\Exceptions\InvalidTaskMessageException;
use App\ConfigModule\Exceptions\TaskNotFoundException;
use App\ConfigModule\Models\SchedulerManager;
use App\ConfigModule\Models\SchedulerMigrationManager;
use App\CoreModule\Exceptions\InvalidJsonException;
use Nette\Utils\FileSystem;
use Nette\Utils\JsonException;

/**
 * Scheduler configuration controller
 * @Path("/scheduler")
 * @Tag("Scheduler configuration manager")
 */
class SchedulerController extends BaseController {

	/**
	 * @var SchedulerManager IQRF Gateway Daemon's scheduler manager
	 */
	private $manager;

	/**
	 * @var SchedulerMigrationManager IQRF Gateway Daemon's scheduler migration manager
	 */
	private $migrationManager;

	/**
	 * Constructor
	 * @param SchedulerManager $manager IQRF Gateway Daemon's scheduler manager
	 * @param SchedulerMigrationManager $migrationManager IQRF Gateway Daemon's scheduler migration manager
	 */
	public function __construct(SchedulerManager $manager, SchedulerMigrationManager $migrationManager) {
		$this->manager = $manager;
		$this->migrationManager = $migrationManager;
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Lists tasks
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/TaskList'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $response->writeJsonBody($this->manager->list());
	}

	/**
	 * @Path("/")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Creates a new task
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/Task'
	 *  responses:
	 *      '201':
	 *          description: Created
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '409':
	 *          description: 'Task already exists'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function create(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$task = $request->getJsonBody(false);
		} catch (JsonException $e) {
			throw new ClientErrorException('Invalid JSON syntax', ApiResponse::S400_BAD_REQUEST);
		}
		$taskId = $task->taskId;
		if ($this->manager->exist($taskId)) {
			$this->manager->getFileName($taskId);
			throw new ClientErrorException('Task already exists', ApiResponse::S409_CONFLICT);
		}
		try {
			$this->manager->save($task, null);
		} catch (InvalidTaskMessageException $e) {
			throw new ClientErrorException('Invalid mType', ApiResponse::S400_BAD_REQUEST);
		} catch (JsonException $e) {
			throw new ClientErrorException('Invalid JSON syntax', ApiResponse::S400_BAD_REQUEST);
		}
		return $response->withStatus(ApiResponse::S201_CREATED)
			->writeBody('Workaround');
	}

	/**
	 * @Path("/{taskId}")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns task configuration
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/Task'
	 *      '404':
	 *          description: Not found
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="taskId", type="integer", description="Task ID")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		if (!is_numeric($request->getParameter('taskId'))) {
			throw new ClientErrorException('Invalid task ID', ApiResponse::S400_BAD_REQUEST);
		}
		$taskId = (int) $request->getParameter('taskId');
		try {
			$task = (array) $this->manager->load($taskId);
			return $response->writeJsonBody($task);
		} catch (TaskNotFoundException $e) {
			throw new ClientErrorException('Task not found', ApiResponse::S404_NOT_FOUND);
		}
	}

	/**
	 * @Path("/{taskId}")
	 * @Method("DELETE")
	 * @OpenApi("
	 *  summary: Deletes a task
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '404':
	 *          description: 'Task not found'
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="taskId", type="integer", description="Task ID")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function delete(ApiRequest $request, ApiResponse $response): ApiResponse {
		if (!is_numeric($request->getParameter('taskId'))) {
			throw new ClientErrorException('Invalid task ID', ApiResponse::S400_BAD_REQUEST);
		}
		$taskId = (int) $request->getParameter('taskId');
		try {
			$this->manager->delete($taskId);
			return $response->writeBody('Workaround');
		} catch (TaskNotFoundException $e) {
			throw new ClientErrorException('Task not found', ApiResponse::S404_NOT_FOUND);
		}
	}

	/**
	 * @Path("/{taskId}")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Edits a task
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/Task'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '404':
	 *          description: 'Task not found'
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="taskId", type="integer", description="Task ID")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		if (!is_numeric($request->getParameter('taskId'))) {
			throw new ClientErrorException('Invalid task ID', ApiResponse::S400_BAD_REQUEST);
		}
		$taskId = (int) $request->getParameter('taskId');
		try {
			$task = $request->getJsonBody(false);
		} catch (JsonException $e) {
			throw new ClientErrorException('Invalid JSON syntax', ApiResponse::S400_BAD_REQUEST);
		}
		try {
			$fileName = $this->manager->getFileName($taskId);
		} catch (TaskNotFoundException $e) {
			throw new ClientErrorException('Task not found', ApiResponse::S404_NOT_FOUND);
		}
		try {
			$this->manager->save($task, $fileName);
		} catch (InvalidTaskMessageException $e) {
			throw new ClientErrorException('Invalid mType', ApiResponse::S400_BAD_REQUEST);
		} catch (JsonException $e) {
			throw new ServerErrorException('Invalid JSON', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		}
		return $response->writeBody('Workaround');
	}

	/**
	 * @Path("/export")
	 * @Method("GET")
	 * @OpenApi("
	 *   summary: Exports scheduler configuration
	 *   responses:
	 *     '200':
	 *       description: 'Success'
	 *       content:
	 *         application/zip:
	 *           schema:
	 *             type: string
	 *             format: binary
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function export(ApiRequest $request, ApiResponse $response): ApiResponse {
		$path = $this->migrationManager->createArchive();
		$fileName = basename($path);
		$response->writeBody(FileSystem::read($path));
		return FileResponseAdjuster::adjust($response, $response->getBody(), $fileName, 'application/zip');
	}

	/**
	 * @Path("/import")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Imports scheduler configuration
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/Task'
	 *          application/zip:
	 *              schema:
	 *                  type: string
	 *                  format: binary
	 *  responses:
	 *      '200':
	 *          description: 'Success'
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '415':
	 *          description: 'Unsupported media type'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function import(ApiRequest $request, ApiResponse $response): ApiResponse {
		$contentType = $request->getHeader('Content-Type')[0] ?? null;
		switch ($contentType) {
			case 'application/zip':
				$path = '/tmp/iqrf-gateway-scheduler-upload.zip';
				FileSystem::write($path, $request->getBody()->getContents());
				try {
					$this->migrationManager->extractArchive($path);
				} catch (InvalidTaskMessageException $e) {
					throw new ClientErrorException('Invalid mType', ApiResponse::S400_BAD_REQUEST);
				} catch (JsonException $e) {
					throw new ClientErrorException('Invalid JSON syntax', ApiResponse::S400_BAD_REQUEST);
				} catch (InvalidJsonException $e) {
					throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST);
				}
				FileSystem::delete($path);
				break;
			case 'application/json':
				try {
					$this->manager->save($request->getJsonBody(false), null);
				} catch (InvalidTaskMessageException $e) {
					throw new ClientErrorException('Invalid mType', ApiResponse::S400_BAD_REQUEST);
				} catch (JsonException $e) {
					throw new ClientErrorException('Invalid JSON syntax', ApiResponse::S400_BAD_REQUEST);
				} catch (InvalidJsonException $e) {
					throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST);
				}
				break;
			default:
				throw new ClientErrorException('Unsupported media type', ApiResponse::S415_UNSUPPORTED_MEDIA_TYPE);
		}
		return $response->writeBody('Workaround');
	}

}
