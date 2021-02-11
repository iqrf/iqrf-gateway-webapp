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
use App\Models\Database\Entities\WireguardInterface;
use App\Models\Database\Entities\WireguardPeer;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\WireguardInterfaceRepository;
use App\NetworkModule\Exceptions\WireguardKeyErrorException;
use App\ServiceModule\Models\ServiceManager;
use Darsyn\IP\Version\Multi;
use Doctrine\Common\Collections\ArrayCollection;
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
	 * @var EntityManager Entity manager
	 */
	private $entityManager;

	/**
	 * @var WireguardInterfaceRepository;
	 */
	private $repository;

	/**
	 * @var ServiceManager Service manager
	 */
	private $serviceManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param EntityManager $entityManager Entity manager
	 * @param ServiceManager $serviceManager Service manager
	 */
	public function __construct(CommandManager $commandManager, EntityManager $entityManager, ServiceManager $serviceManager) {
		$this->commandManager = $commandManager;
		$this->entityManager = $entityManager;
		$this->repository = $this->entityManager->getWireguardInterfaceRepository();
		$this->serviceManager = $serviceManager;
	}

	/**
	 * Returns list of existing Wireguard tunnel configurations
	 * @return array<int, array<string, string|bool>> List of Wireguard tunnels
	 */
	public function listTunnels(): array {
		return $this->repository->findAll();
	}

	/**
	 * Returns configuration of Wireguard tunnel
	 * @param string $id Wireguard interface id
	 * @return WireguardInterface Wireguard tunnel configuration
	 */
	public function getTunnel(string $id): WireguardInterface {
		return $this->repository->find($id);
	}

	/**
	 * Adds a new Wireguard tunnel
	 * @param stdClass $values New Wireguard tunnel configuration
	 */
	public function createTunnel(stdClass $values): void {
		$peers = [];
		foreach ($values->peers as $peer) {
			$peers[] = new WireguardPeer($peer->publicKey, $peer->psk ?? null, $peer->keepalive, $peer->endpoint, $peer->port, null);
		}
		$peers = new ArrayCollection($peers);
		$interface = new WireguardInterface($values->name, $values->privateKey, $values->port, Multi::factory($values->ipv4), $values->ipv4Prefix, Multi::factory($values->ipv6), $values->ipv6Prefix, $peers);
		$this->entityManager->persist($interface);
		$this->entityManager->flush();
	}

	/**
	 * Removes an existing Wireguard tunnel
	 * @param string $name Wireguard tunnel name
	 */
	public function removeTunnel(string $name): void {
		//TODO
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

}
