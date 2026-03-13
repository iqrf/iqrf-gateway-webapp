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

use App\SecurityModule\Exceptions\DaemonApiTokenManagerException;
use App\SecurityModule\Exceptions\DaemonApiTokenNotFoundException;
use App\SecurityModule\Exceptions\DaemonApiTokenNotValidException;
use Iqrf\CommandExecutor\CommandExecutor;
use Nette\Utils\Json;
use stdClass;

/**
 * Daemon API token manager
 */
class DaemonApiTokenManager {

	/**
	 * CLI utility command
	 */
	public const COMMAND = 'iqrfgd2-tokenctl';

	/**
	 * Constructor
	 * @param CommandExecutor $commandManager Command manager
	 */
	public function __construct(
		private readonly CommandExecutor $commandManager
	) {
	}

	/**
	 * Lists Daemon API access tokens
	 * @return string JSON-serialized string of access token list
	 * @throws DaemonApiTokenManagerException Raised if an unexpected error occurs
	 */
	public function listTokens(): string {
		$this->checkUtility();
		$result = $this->commandManager->run(
			command: self::COMMAND . ' list -j',
			needSudo: true,
		);
		if ($result->getExitCode() !== 0) {
			throw new DaemonApiTokenManagerException($result->getStderr());
		}
		return $result->getStdout();
	}

	/**
	 * Retrieves Daemon API access token
	 * @param int $id Access token record ID
	 * @return string JSON-serialized string of access token
	 * @throws DaemonApiTokenManagerException Raised if an internal error has occurred
	 * @throws DaemonApiTokenNotFoundException Raised if an access token record does not exist
	 */
	public function getToken(int $id): string {
		$this->checkUtility();
		$result = $this->commandManager->run(
			command: self::COMMAND . ' get -j -i ' . strval($id),
			needSudo: true,
		);
		$exitCode = $result->getExitCode();
		if ($exitCode === 0) {
			return $result->getStdout();
		}
		if ($exitCode === 3) {
			throw new DaemonApiTokenNotFoundException($result->getStderr());
		}
		throw new DaemonApiTokenManagerException($result->getStderr());
	}

	/**
	 * Creates a new Daemon API access token
	 * @param stdClass $data Token data
	 * @return array{
	 *  id: int,
	 *  token: string,
	 * } Created token
	 * @throws DaemonApiTokenManagerException Raised if an internal error has ocurred
	 */
	public function createToken(stdClass $data): array {
		$this->checkUtility();
		$result = $this->commandManager->run(
			command: sprintf(
				'%s create -o %s -e %s -j',
				self::COMMAND,
				escapeshellarg($data->owner),
				property_exists($data, 'expiration') ? $data->expiration : strval($data->count) . $data->unit,
			),
			needSudo: true,
		);
		if ($result->getExitCode() !== 0) {
			throw new DaemonApiTokenManagerException($result->getStderr());
		}
		return Json::decode($result->getStdout(), forceArrays: true);
	}

	/**
	 * Revokes a valid token by ID
	 *
	 * Attempting to revoke an expired, or already revoked token is equivalent to no-op.
	 *
	 * @param int $id Access token record ID
	 * @throws DaemonApiTokenManagerException Raised if an internal error has occurred
	 * @throws DaemonApiTokenNotFoundException Raised if a token record does not exist
	 */
	public function revokeToken(int $id): void {
		$this->checkUtility();
		$result = $this->commandManager->run(
			command: self::COMMAND . ' revoke -i ' . strval($id),
			needSudo: true,
		);
		$exitCode = $result->getExitCode();
		if ($exitCode === 0 || $exitCode === 4 || $exitCode === 5) {
			return;
		}
		if ($exitCode === 3) {
			throw new DaemonApiTokenNotFoundException($result->getStderr());
		}
		throw new DaemonApiTokenManagerException($result->getStderr());
	}

	/**
	 * Rotates a valid token by token ID
	 * @param int $id Access token record ID
	 * @return array{
	 *  id: int,
	 *  token: string,
	 * } New token after rotating
	 * @throws DaemonApiTokenManagerException Raised if an internal error has occurred
	 * @throws DaemonApiTokenNotFoundException Raised if a token record does not exist
	 * @throws DaemonApiTokenNotValidException Raised if a token is not valid
	 */
	public function rotateToken(int $id): array {
		$this->checkUtility();
		$result = $this->commandManager->run(
			command: self::COMMAND . ' rotate -j -i ' . strval($id),
			needSudo: true,
		);
		$exitCode = $result->getExitCode();
		if ($exitCode === 0) {
			return Json::decode($result->getStdout(), forceArrays: true);
		}
		if ($exitCode === 3) {
			throw new DaemonApiTokenNotFoundException($result->getStderr());
		}
		if ($exitCode === 4 || $exitCode === 5) {
			throw new DaemonApiTokenNotValidException($result->getStderr());
		}
		throw new DaemonApiTokenManagerException($result->getStderr());
	}

	/**
	 * Checks that CLI utility exists
	 * @throws DaemonApiTokenManagerException Raised if CLI utility does not exist
	 */
	public function checkUtility(): void {
		if (!$this->commandManager->commandExist(self::COMMAND)) {
			throw new DaemonApiTokenManagerException('Daemonn API access token utility not available.');
		}
	}

}
