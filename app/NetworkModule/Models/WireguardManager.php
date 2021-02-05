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
use App\NetworkModule\Enums\InterfaceTypes;
use App\NetworkModule\Exceptions\IpKernelException;
use App\NetworkModule\Exceptions\IpSyntaxException;
use App\NetworkModule\Exceptions\NonexistentDeviceException;
use App\NetworkModule\Exceptions\WireguardKeyExistsException;
use Nette\Utils\FileSystem;

/**
 * Wireguard VPN manager
 */
class WireguardManager {

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var InterfaceManager Interface manager
	 */
	private $interfaceManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(CommandManager $commandManager, InterfaceManager $interfaceManager) {
		$this->commandManager = $commandManager;
		$this->interfaceManager = $interfaceManager;
	}

	/**
	 * Creates a new Wireguard VPN interface
	 * @param string $name New interface name
	 */
	public function createInterface(string $name): void {
		$command = sprintf('ip link add dev %s type wireguard', $name);
		$output = $this->commandManager->run($command, true);
		$exitCode = $output->getExitCode();
		if ($exitCode !== 0) {
			$this->handleIpError($exitCode, $output->getStderr());
		}
	}

	/**
	 * Removes an existing Wireguard VPN interface
	 * @param string $name Interface name
	 */
	public function removeInterface(string $name): void {
		$interfaces = $this->interfaceManager->list(InterfaceTypes::WIREGUARD());
		$match = null;
		foreach ($interfaces as $iface) {
			if ($name === $iface->getName()) {
				$match = $iface;
				break;
			}
		}
		if ($match === null) {
			throw new NonexistentDeviceException();
		}
		$command = sprintf('ip link delete %s', $name);
		$output = $this->commandManager->run($command, true);
		$exitCode = $output->getExitCode();
		if ($exitCode !== 0) {
			$this->handleIpError($exitCode, $output->getStderr());
		}
	}

	/**
	 * Lists all existing Wireguard key pairs
	 * @return array<int, string> List of key pair names
	 */
	public function listKeys(): array {
		$files = scandir('/etc/wireguard/keys/');
		$keys = [];
		foreach ($files as $file) {
			$filename = pathinfo($file, PATHINFO_FILENAME);
			if (in_array($filename, $keys, true)) {
				continue;
			}
			array_push($keys, $filename);
		}
		return $keys;
	}

	/**
	 * Generates Wireguard key pair
	 * @return array<string, string> New key pair
	 */
	public function generateKeys(): array {
		$privateKey = $this->generatePrivateKey();
		$publicKey = $this->generatePublicKey($privateKey);
		return [
			'privateKey' => $privateKey,
			'publicKey' => $publicKey
		];
	}

	/**
	 * Generates Wireguard private key
	 * @return string Wireguard private key
	 */
	public function generatePrivateKey(): string {
		$output = $this->commandManager->run('umask 077 && wg genkey', false);
		if ($output->getExitCode() !== 0) {
			throw new WireguardKeyExistsException($output->getStderr());
		}
		return $output->getStdout();
	}

	/**
	 * Derives Wireguard public key from private key
	 * @param string $privateKey Private key to derive public key from
	 * @return string Wireguard public key
	 */
	public function generatePublicKey(string $privateKey): string {
		$output = $this->commandManager->run('wg pubkey', false, $privateKey);
		if ($output->getExitCode() !== 0) {
			throw new WireguardKeyExistsException($output->getStderr());
		}
		return $output->getStdout();
	}

	/**
	 * Removes an existing wireguard key pair
	 */
	public function removeKeys(string $name): void {
		$path = '/etc/wireguard/keys/' . $name;
		FileSystem::delete($path . '.privatekey');
		FileSystem::delete($path . '.publickey');
	}

	/**
	 * Handles ip-link errors
	 * @param int $exitCode Exit code
	 * @param string $error Error message
	 * @throws IpSyntaxException
	 * @throws IpKernelException
	 */
	private function handleIpError(int $exitCode, string $error): void {
		if ($exitCode === 1) {
			throw new IpSyntaxException($error);
		}
		throw new IpKernelException($error);
	}

}
