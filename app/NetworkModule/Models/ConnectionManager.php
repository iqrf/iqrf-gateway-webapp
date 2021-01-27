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

namespace App\NetworkModule\Models;

use App\CoreModule\Models\CommandManager;
use App\NetworkModule\Entities\Connection;
use App\NetworkModule\Entities\ConnectionDetail;
use App\NetworkModule\Enums\ConnectionTypes;
use App\NetworkModule\Exceptions\NetworkManagerException;
use App\NetworkModule\Exceptions\NonexistentConnectionException;
use Ramsey\Uuid\UuidInterface;
use stdClass;

/**
 * Network connection manager
 */
class ConnectionManager {

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

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
		return ConnectionDetail::nmCliDeserialize($output->getStdout());
	}

	/**
	 * Returns current automatic IPv4 configuration
	 * @param UuidInterface $uuid Network connection UUID
	 * @return array<string, string|array<int, string|array<string, int|string>>> Current IPv4 automatic configuration
	 */
	public function getIpv4Auto(UuidInterface $uuid): array {
		$config = $this->getAutoConfig($uuid, 'IP4');
		foreach ($config as $item) {
			if (strpos($item, 'IP4.ADDRESS[1]') !== false) {
				$address = explode(':', $item)[1];
			} elseif (strpos($item, 'IP4.GATEWAY') !== false) {
				$gateway = explode(':', $item)[1];
			} elseif (strpos($item, 'IP4.DNS[1]') !== false) {
				$dns = explode(':', $item)[1];
			}
		}
		$prefix = isset($address) ? intval(explode('/', $address)[1]) : null;
		$mask = str_split(str_repeat('1', $prefix) . str_repeat('0', 32 - $prefix), 8);
		foreach ($mask as &$number) {
			$number = bindec($number);
		}
		return [
			'method' => 'auto',
			'addresses' => [[
				'address' => isset($address) ? explode('/', $address)[0] : null,
				'mask' => isset($prefix) ? implode('.', $mask) : null,
				'prefix' => $prefix,
			]],
			'gateway' => $gateway ?? null,
			'dns' => [[
				'address' => $dns ?? null,
			]],
		];
	}

	/**
	 * Returns current automatic IPv6 configuration
	 * @param UuidInterface $uuid Network connection UUID
	 * @return array<string, string|array<int, string|array<string, int|string>>> Current IPv6 automatic configuration
	 */
	public function getIpv6Auto(UuidInterface $uuid): array {
		$config = $this->getAutoConfig($uuid, 'IP6');
		$addresses = [];
		$gateways = [];
		$dns = [];
		foreach ($config as $item) {
			if (strpos($item, 'IP6.ADDRESS') !== false) {
				array_push($addresses, explode(']:', $item)[1]);
			} elseif (strpos($item, 'IP6.GATEWAY') !== false) {
				array_push($gateways, $item);
			} elseif (strpos($item, 'IP6.DNS') !== false) {
				array_push($dns, ['address' => explode(']:', $item)[1]]);
			}
		}
		$array = [
			'method' => 'auto',
			'addresses' => [],
			'dns' => count($dns) === 0 ? [['address' => null]] : $dns,
		];
		foreach ($addresses as $item) {
			$tokens = explode('/', $item);
			array_push($array['addresses'], ['address' => $tokens[0], 'prefix' => intval($tokens[1]), 'gateway' => null]);
		}
		if (count($gateways) === 1) {
			foreach ($array['addresses'] as $item) {
				$item['gateway'] = $gateways[0];
			}
		} elseif (count($gateways) > 1) {
			foreach ($gateways as $item) {
				$index = explode('[', $item)[1][0];
				$array['addresses'][$index]['gateway'] = explode(']:', $item)[1];
			}
		}
		return $array;
	}

	/**
	 * Returns connection configuration properties filtered by protocol
	 * @param UuidInterface $uuid Network connection UUID
	 * @param string $protocol Internet protocol version
	 * @return array<int, string> Filtered connection configuration
	 */
	private function getAutoConfig(UuidInterface $uuid, string $protocol): array {
		$output = $this->commandManager->run('nmcli -t -s connection show ' . $uuid->toString() . ' | grep ' . $protocol, true);
		$exitCode = $output->getExitCode();
		if ($exitCode !== 0) {
			$this->handleError($exitCode, $output->getStderr());
		}
		return explode(PHP_EOL, $output->getStdout());
	}


	/**
	 * Lists the network connections
	 * @param ConnectionTypes|null $type Network connection type
	 * @return array<Connection> Network connections
	 */
	public function list(?ConnectionTypes $type = null): array {
		$output = $this->commandManager->run('nmcli -t connection show', true)->getStdout();
		if ($output === '') {
			return [];
		}
		$array = explode(PHP_EOL, trim($output));
		$connections = [];
		foreach ($array as $row) {
			$connection = Connection::nmCliDeserialize($row);
			if ($type === null || $type->equals($connection->getType())) {
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
		$pattern = '/[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}/';
		preg_match($pattern, $output->getStdout(), $matches);
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
		$configuration = $newConnection->nmCliSerialize();
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
