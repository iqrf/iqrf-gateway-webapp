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
use App\CoreModule\Models\FileManager;
use App\NetworkModule\Entities\WireguardTunnel;
use App\NetworkModule\Enums\InterfaceTypes;
use App\NetworkModule\Exceptions\InterfaceExistsException;
use App\NetworkModule\Exceptions\IpKernelException;
use App\NetworkModule\Exceptions\IpSyntaxException;
use App\NetworkModule\Exceptions\WireguardKeyErrorException;
use App\NetworkModule\Exceptions\WireguardKeyMismatchException;
use App\ServiceModule\Models\ServiceManager;
use stdClass;

/**
 * Wireguard VPN manager
 */
class WireguardManager {

	/**
	 * Wireguard directory
	 */
	private const DIR = '/etc/wireguard/';

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var FileManager File manager
	 */
	private $fileManager;

	/**
	 * @var InterfaceManager Interface manager
	 */
	private $interfaceManager;

	/**
	 * @var ServiceManager Service manager
	 */
	private $serviceManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param FileManager $fileManager File manager
	 * @param InterfaceManager $interfaceManager Interface manager
	 * @param ServiceManager $serviceManager Service manager
	 */
	public function __construct(CommandManager $commandManager, FileManager $fileManager, InterfaceManager $interfaceManager, ServiceManager $serviceManager) {
		$this->commandManager = $commandManager;
		$this->fileManager = $fileManager;
		$this->interfaceManager = $interfaceManager;
		$this->serviceManager = $serviceManager;
	}

	/**
	 * Returns list of existing wireguard tunnel configurations
	 * @return array<int, array<string, string|bool>> List of Wireguard tunnels
	 */
	public function listTunnels(): array {
		$files = scandir(self::DIR);
		$array = [];
		foreach ($files as $file) {
			if (pathinfo($file, PATHINFO_EXTENSION) === 'conf') {
				$filename = pathinfo($file, PATHINFO_FILENAME);
				$output = $this->commandManager->run('wg show ' . $filename, true);
				$array[] = ['name' => $filename, 'active' => ($output->getExitCode() === 0)];
			}
		}
		return $array;
	}

	/**
	 * Adds a new Wireguard tunnel
	 * @param stdClass $values New Wireguard tunnel configuration
	 */
	public function createTunnel(stdClass $values): void {
		if (!$this->validatePublicKey($values->privateKey, $values->publicKey)) {
			throw new WireguardKeyMismatchException('Supplied private key and public key do not match.');
		}
		//$this->createInterface($values->name); not wg-quick
		//$keyFiles = $this->storeKeys($values->name, $values->privateKey, $values->publicKey);
		$tunnel = WireguardTunnel::jsonDeserialize($values);
		//$this->commandManager->run($tunnel->wgSerialize(), true); not wg-quick
		//$this->commandManager->run($tunnel->ipSerialize(), true); not wg-quick
		$this->fileManager->write($tunnel->getName() . '.conf', $tunnel->toConf());
	}

	/**
	 * Activates Wireguard tunnel specified by name
	 * @param stdClass $config Wireguard state configuration
	 */
	public function changeTunnelState(stdClass $config): void {
		$serviceName = 'wg-quick@' . $config->name;
		if ($config->enabled) {
			if (!$this->serviceManager->isEnabled($serviceName)) {
				$this->serviceManager->enable($serviceName);
			}
		} else {
			if ($this->serviceManager->isEnabled($serviceName)) {
				$this->serviceManager->disable($serviceName);
			}
		}
	}

	/**
	 * Creates a new wireguard interface
	 * @param string $name Interface name
	 */
	public function createInterface(string $name): void {
		$interfaces = $this->interfaceManager->list(InterfaceTypes::WIREGUARD());
		foreach ($interfaces as $iface) {
			if ($iface->getName() === $name) {
				throw new InterfaceExistsException('Interface ' . $name . ' already exists.');
			}
		}
		$command = sprintf('ip link add dev %s type wireguard', $name);
		$output = $this->commandManager->run($command, true);
		$exitCode = $output->getExitCode();
		if ($exitCode !== 0) {
			$this->handleIpErrors($exitCode, $output->getStderr());
		}
	}

	/**
	 * Stores keypair in files
	 * @param string $name Keypair name
	 * @param string $privateKey Private key
	 * @param string $publicKey Public key
	 * @return array<string, string> Path to keypair files
	 */
	public function storeKeys(string $name, string $privateKey, string $publicKey): array {
		$privateKeyFile = $name . '.privatekey';
		$publicKeyFile = $name . '.publickey';
		$this->fileManager->write($privateKeyFile, $privateKey);
		$this->fileManager->write($publicKeyFile, $publicKey);
		return [
			'privateKey' => self::DIR . $privateKeyFile,
			'publicKey' => self::DIR . $publicKeyFile,
		];
	}

	/**
	 * Generates Wireguard keypair
	 * @return array<string, string> New key pair
	 */
	public function generateKeys(): array {
		$privateKey = $this->generatePrivateKey();
		$publicKey = $this->generatePublicKey($privateKey);
		return [
			'privateKey' => $privateKey,
			'publicKey' => $publicKey,
		];
	}

	/**
	 * Generates Wireguard private key
	 * @return string Wireguard private key
	 */
	public function generatePrivateKey(): string {
		$output = $this->commandManager->run('umask 077 && wg genkey', false);
		if ($output->getExitCode() !== 0) {
			throw new WireguardKeyErrorException($output->getStderr());
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
			throw new WireguardKeyErrorException($output->getStderr());
		}
		return $output->getStdout();
	}

	/**
	 * Checks if supplied public key has been derived from supplied private key
	 * @param string $privateKey Wireguard tunnel interface private key
	 * @param string $publicKey Wireguard tunnel interface public key
	 * @return bool true if public key matches private key, false otherwise
	 */
	private function validatePublicKey(string $privateKey, string $publicKey): bool {
		$output = $this->commandManager->run('wg pubkey', false, $privateKey);
		if ($output->getExitCode() !== 0) {
			return false;
		}
		return $output->getStdout() === $publicKey;
	}

	/**
	 * Handles ip utility errors
	 * @param int $exitCode IP exit code
	 * @param string $message Error message
	 */
	private function handleIpErrors(int $exitCode, string $message): void {
		if ($exitCode === 1) {
			throw new IpSyntaxException($message);
		}
		throw new IpKernelException($message);
	}

}
