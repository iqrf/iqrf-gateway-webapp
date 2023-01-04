<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

namespace App\NetworkModule\Models;

use App\CoreModule\Models\CommandManager;
use App\NetworkModule\Entities\Connection;
use App\NetworkModule\Entities\ConnectionDetail;
use App\NetworkModule\Enums\ConnectionTypes;
use App\NetworkModule\Exceptions\NetworkManagerException;
use App\NetworkModule\Exceptions\NonexistentConnectionException;
use App\NetworkModule\Utils\NmCliConnection;
use Nette\Utils\Strings;
use Ramsey\Uuid\UuidInterface;
use stdClass;

/**
 * Network connection manager
 */
class ConnectionManager {

	/**
	 * @var CommandManager Command manager
	 */
	private CommandManager $commandManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(CommandManager $commandManager) {
		$this->commandManager = $commandManager;
	}

	/**
	 * Deletes the network connection
	 * @param UuidInterface $uuid Network connection UUID
	 */
	public function delete(UuidInterface $uuid): void {
		$output = $this->commandManager->run('nmcli -t connection delete ' . $uuid->toString(), true);
		$exitCode = $output->getExitCode();
		if ($exitCode !== 0) {
			$this->handleError($exitCode, $output->getStderr());
		}
	}

	/**
	 * Deactivates the connection on the interface
	 * @param UuidInterface $uuid Network connection UUID
	 */
	public function down(UuidInterface $uuid): void {
		$command = sprintf('nmcli -t connection down %s', $uuid->toString());
		$output = $this->commandManager->run($command, true);
		$exitCode = $output->getExitCode();
		if ($exitCode !== 0) {
			$this->handleError($exitCode, $output->getStderr());
		}
	}

	/**
	 * Returns the detailed network connection entity
	 * @param UuidInterface $uuid Network connection UUID
	 * @return ConnectionDetail Detailed network connection entity
	 */
	public function get(UuidInterface $uuid): ConnectionDetail {
		$output = $this->commandManager->run('nmcli -t -s connection show ' . $uuid->toString(), true);
		$exitCode = $output->getExitCode();
		if ($exitCode !== 0) {
			$this->handleError($exitCode, $output->getStderr());
		}
		$nmcli = NmCliConnection::decode($output->getStdout());
		return ConnectionDetail::nmCliDeserialize($nmcli);
	}

	/**
	 * Lists the network connections
	 * @param ConnectionTypes|null $type Network connection type
	 * @return array<Connection> Network connections
	 */
	public function list(?ConnectionTypes $type = null): array {
		$fields = ['NAME', 'UUID', 'TYPE', 'DEVICE'];
		$command = sprintf('nmcli -t -f %s connection show', implode(',', $fields));
		$output = $this->commandManager->run($command, true)->getStdout();
		if ($output === '') {
			return [];
		}
		$array = explode(PHP_EOL, trim($output));
		$connections = [];
		foreach ($array as $row) {
			$connection = Connection::nmCliDeserialize($row);
			if ($type === null || $type->equals($connection->getType())) {
				if ($type === ConnectionTypes::WIFI()) {
					$detail = $this->get($connection->getUuid())->jsonSerialize();
					$bssids = $detail['wifi']['bssids'];
					$connection = $connection->jsonSerialize();
					$connection['bssids'] = $bssids;
				}
				$connections[] = $connection;
			}
		}
		return $connections;
	}

	/**
	 * Adds a new network connection configuration
	 * @param stdClass $values Network connection configuration from values
	 * @return string UUID of the new connection
	 */
	public function add(stdClass $values): string {
		$newConnection = ConnectionDetail::jsonDeserialize($values);
		$configuration = $newConnection->nmCliSerialize();
		$command = sprintf('nmcli -t connection add %s', $configuration);
		$output = $this->commandManager->run($command, true);
		$exitCode = $output->getExitCode();
		if ($exitCode !== 0) {
			$this->handleError($exitCode, $output->getStderr());
		}
		$pattern = '#[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}#';
		$matches = Strings::match($output->getStdout(), $pattern);
		return $matches[0];
	}

	/**
	 * Edits the network connection's configuration
	 * @param UuidInterface $uuid Network connection UUID
	 * @param stdClass $values Network connection configuration form values
	 */
	public function edit(UuidInterface $uuid, stdClass $values): void {
		$currentConnection = $this->get($uuid);
		$values->id = $currentConnection->getName();
		$values->uuid = $currentConnection->getUuid()->toString();
		$values->type = $currentConnection->getType()->toScalar();
		$values->interface = $currentConnection->getInterfaceName();
		$newConnection = ConnectionDetail::jsonDeserialize($values);
		$configuration = Strings::replace($newConnection->nmCliSerialize(), '#connection\.type \"[\\-\w]+\" #', '');
		$command = sprintf('nmcli -t connection modify %s %s', $uuid->toString(), $configuration);
		$output = $this->commandManager->run($command, true);
		$exitCode = $output->getExitCode();
		if ($exitCode !== 0) {
			$this->handleError($exitCode, $output->getStderr());
		}
	}

	/**
	 * Activates the connection on the interface
	 * @param UuidInterface $uuid Network connection UUID
	 * @param string|null $interface Network interface
	 * @throws NetworkManagerException
	 */
	public function up(UuidInterface $uuid, ?string $interface = null): void {
		$command = sprintf('nmcli -t connection up %s', $uuid->toString());
		if ($interface !== null) {
			$command .= ' ifname ' . $interface;
		}
		$output = $this->commandManager->run($command, true);
		$exitCode = $output->getExitCode();
		if ($exitCode !== 0) {
			$this->handleError($exitCode, $output->getStderr());
		}
	}

	/**
	 * Error handler function for NMCLI
	 * @param int $code NMCLI exit code
	 * @param string $error NMCLI stderr
	 * @throws NetworkManagerException
	 * @throws NonexistentConnectionException
	 */
	private function handleError(int $code, string $error): void {
		if ($code === 10) {
			throw new NonexistentConnectionException($error);
		}
		throw new NetworkManagerException($error);
	}

}
