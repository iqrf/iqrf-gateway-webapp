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
use App\NetworkModule\Entities\WireguardTunnel;
use App\NetworkModule\Exceptions\WireguardKeyExistsException;
use App\NetworkModule\Exceptions\WireguardKeyMismatchException;
use stdClass;

/**
 * Wireguard VPN manager
 */
class WireguardManager {

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
	 * Adds a new Wireguard tunnel
	 * @param stdClass $values New Wireguard tunnel configuration
	 */
	public function createTunnel(stdClass $values): void {
		if (!$this->validatePublicKey($values->privateKey, $values->publicKey)) {
			throw new WireguardKeyMismatchException('Supplied private key and public key do not match.');
		}
		WireguardTunnel::jsonDeserialize($values);
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

}
