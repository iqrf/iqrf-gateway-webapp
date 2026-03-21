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

namespace App\SecurityModule\Models;

use App\SecurityModule\Enums\MosquittoPluginManagerStatusCodes;
use App\SecurityModule\Exceptions\MosquittoPluginManagerException;
use App\SecurityModule\Exceptions\MosquittoPluginManagerInvalidParamsException;
use App\SecurityModule\Exceptions\MosquittoPluginUserNotFoundException;
use Iqrf\CommandExecutor\CommandExecutor;
use Nette\Utils\Json;
use stdClass;

/**
 * Mosquitto plugin manager
 */
class MosquittoPluginManager {

	/**
	 * CLI utility command
	 */
	public const COMMAND = 'mosquitto-plugin-iqrf-manager';

	/**
	 * Constructor
	 * @param CommandExecutor $commandManager Command manager
	 */
	public function __construct(
		private readonly CommandExecutor $commandManager
	) {
	}

	/**
	 * Lists mosquitto plugin users
	 * @return string JSON-serialized string of mosquitto plugin users
	 * @throws MosquittoPluginManagerException Thrown if an unexpected error occurs
	 */
	public function listUsers(): string {
		$this->checkUtility();
		$result = $this->commandManager->run(
			command: self::COMMAND . ' list -j',
			needSudo: true,
		);
		if ($result->getExitCode() !== 0) {
			throw new MosquittoPluginManagerException($result->getStderr());
		}
		return $result->getStdout();
	}

	/**
	 * Retrieves mosquitto plugin user
	 * @param int $id User record ID
	 * @return string JSON-serialized string of mosquitto plugin user
	 * @throws MosquittoPluginUserNotFoundException Thrown if user record does not exist
	 * @throws MosquittoPluginManagerException Thrown if an unexpected error occurs
	 */
	public function getUser(int $id): string {
		$this->checkUtility();
		$result = $this->commandManager->run(
			command: self::COMMAND . ' get -j -i ' . strval($id),
			needSudo: true,
		);
		$exitCode = $result->getExitCode();
		if ($exitCode === 0) {
			return $result->getStdout();
		}
		if ($exitCode === MosquittoPluginManagerStatusCodes::USER_NOT_FOUND->value) {
			throw new MosquittoPluginUserNotFoundException($result->getStderr());
		}
		throw new MosquittoPluginManagerException($result->getStderr());
	}

	/**
	 * Creates a new mosquitto plugin user
	 * @param stdClass $data User data
	 * @return array{
	 *  id: int,
	 *  username: string,
	 *  createdAt: string,
	 *  state: int,
	 *  blockedAt: string|null
	 * } Created user
	 * @throws MosquittoPluginManagerInvalidParamsException Thrown if the utility is invoked with invalid parameters
	 * @throws MosquittoPluginManagerException Thrown if an unexpected error occurs
	 */
	public function createUser(stdClass $data): array {
		$this->checkUtility();
		$result = $this->commandManager->run(
			command: sprintf(
				'%s create -u %s -p %s -j',
				self::COMMAND,
				escapeshellarg($data->username),
				escapeshellarg($data->password),
			),
			needSudo: true,
		);
		$exitCode = $result->getExitCode();
		if ($exitCode === 0) {
			return Json::decode($result->getStdout(), forceArrays: true);
		}
		if ($exitCode === MosquittoPluginManagerStatusCodes::INVALID_PARAMS->value) {
			throw new MosquittoPluginManagerInvalidParamsException($result->getStderr());
		}
		throw new MosquittoPluginManagerException($result->getStderr());
	}

	/**
	 * Blocks a valid user by ID
	 *
	 * Attempting to block an already blocked user is equivalent to no-op.
	 *
	 * @param int $id User record ID
	 * @throws MosquittoPluginUserNotFoundException Raised if user record does not exist
	 * @throws MosquittoPluginManagerException Raised if an unexpected error occurs
	 */
	public function blockUser(int $id): void {
		$this->checkUtility();
		$result = $this->commandManager->run(
			command: self::COMMAND . ' block -i ' . strval($id),
			needSudo: true,
		);
		$exitCode = $result->getExitCode();
		if ($exitCode === 0 ||
			$exitCode === MosquittoPluginManagerStatusCodes::USER_BLOCKED->value
		) {
			return;
		}
		if ($exitCode === MosquittoPluginManagerStatusCodes::USER_NOT_FOUND->value) {
			throw new MosquittoPluginUserNotFoundException($result->getStderr());
		}
		throw new MosquittoPluginManagerException($result->getStderr());
	}

	/**
	 * Checks that CLI utility exists
	 * @throws MosquittoPluginManagerException Raised if CLI utility does not exist
	 */
	public function checkUtility(): void {
		if (!$this->commandManager->commandExist(self::COMMAND)) {
			throw new MosquittoPluginManagerException('Mosquitto plugin manager utility not available.');
		}
	}

}
