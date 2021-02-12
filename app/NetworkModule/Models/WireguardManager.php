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
use App\Models\Database\Entities\WireguardPeerAddress;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\WireguardInterfaceRepository;
use App\Models\Database\Repositories\WireguardPeerAddressRepository;
use App\Models\Database\Repositories\WireguardPeerRepository;
use App\NetworkModule\Exceptions\NonexistentWireguardTunnelException;
use App\NetworkModule\Exceptions\WireguardKeyErrorException;
use Darsyn\IP\Version\Multi;
use Doctrine\Common\Collections\ArrayCollection;
use stdClass;
use function assert;

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
	 * @var WireguardInterfaceRepository Wireguard interface repository
	 */
	private $wireguardInterfaceRepository;

	/**
	 * @var WireguardPeerAddressRepository Wireguard peer address repository
	 */
	private $wireguardPeerAddressRepository;

	/**
	 * @var WireguardPeerRepository Wireguard peer repository
	 */
	private $wireguardPeerRepository;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param EntityManager $entityManager Entity manager
	 */
	public function __construct(CommandManager $commandManager, EntityManager $entityManager) {
		$this->commandManager = $commandManager;
		$this->entityManager = $entityManager;
		$this->wireguardInterfaceRepository = $this->entityManager->getWireguardInterfaceRepository();
		$this->wireguardPeerAddressRepository = $this->entityManager->getWireguardPeerAddressRepository();
		$this->wireguardPeerRepository = $this->entityManager->getWireguardPeerRepository();
	}

	/**
	 * Returns list of existing Wireguard interfaces configurations
	 * @return array<int, array<string, bool|int|string|null>> List of Wireguard interfaces
	 */
	public function listInterfaces(): array {
		$array = [];
		foreach ($this->wireguardInterfaceRepository->findAll() as $interface) {
			assert($interface instanceof WireguardInterface);
			$array[] = [
				'id' => $interface->getId(),
				'name' => $interface->getName(),
				'active' => $this->isInterfaceActive($interface->getName()),
			];
		}
		return $array;
	}

	/**
	 * Checks if interface is active
	 */
	public function isInterfaceActive(string $name): bool {
		$output = $this->commandManager->run('wg show ' . $name, true);
		return $output->getExitCode() === 0;
	}

	/**
	 * Returns configuration of Wireguard interface
	 * @param int $id Wireguard interface id
	 * @return WireguardInterface Wireguard interface configuration
	 */
	public function getInterface(int $id): WireguardInterface {
		$interface = $this->wireguardInterfaceRepository->find($id);
		if ($interface === null) {
			throw new NonexistentWireguardTunnelException('Wireguard tunnel not found');
		}
		assert($interface instanceof WireguardInterface);
		return $interface;
	}

	/**
	 * Adds a new Wireguard interface
	 * @param stdClass $values New Wireguard interface configuration
	 */
	public function createInterface(stdClass $values): void {
		$peers = [];
		$interface = new WireguardInterface($values->name, $values->privateKey, $values->port, Multi::factory($values->ipv4), $values->ipv4Prefix, Multi::factory($values->ipv6), $values->ipv6Prefix, new ArrayCollection($peers));
		foreach ($values->peers as $peer) {
			$peers[] = $this->createPeer($peer, $interface);
		}
		$interface->setPeers(new ArrayCollection($peers));
		$this->entityManager->persist($interface);
		$this->entityManager->flush();
	}

	/**
	 * Edits an existing Wireguard interface
	 * @param int $id Wireguard interface ID
	 * @param stdClass $values Wireguard interface configuration
	 */
	public function editInterface(int $id, stdClass $values): void {
		$interface = $this->getInterface($id);
		$interface->setName($values->name);
		$interface->setPrivateKey($values->privateKey);
		$interface->setPort($values->port);
		$interface->setIpv4(Multi::factory($values->ipv4));
		$interface->setIpv4Prefix($values->ipv4Prefix);
		$interface->setIpv6(Multi::factory($values->ipv6));
		$interface->setIpv6Prefix($values->ipv6Prefix);
		$peers = [];
		foreach ($values->peers as $peer) {
			if ($peer->id !== null) {
				$ifPeer = $this->wireguardPeerRepository->find($peer->id);
				if ($ifPeer === null) {
					throw new NonexistentWireguardTunnelException('Wireguard peer not found');
				}
				$addresses = $this->updatePeerAddresses($peer->allowedIPs->ipv4, $ifPeer);
				$addresses = array_merge($addresses, $this->updatePeerAddresses($peer->allowedIPs->ipv6, $ifPeer));
				$ifPeer->setPublicKey($peer->publicKey);
				$ifPeer->setPsk($peer->psk ?? null);
				$ifPeer->setKeepalive($peer->keepalive);
				$ifPeer->setEndpoint($peer->endpoint);
				$ifPeer->setPort($peer->port);
				$ifPeer->setAddresses(new ArrayCollection($addresses));
			} else {
				$peers[] = $this->createPeer($peer, $interface);
			}
		}
		$interface->setPeers(new ArrayCollection($peers));
		$this->entityManager->merge($interface);
		$this->entityManager->flush();
	}

	/**
	 * Creates a wireguard peer entity
	 * @param stdClass $peer Peer entity configuration
	 * @param WireguardInterface $interface Wireguard interface
	 * @return WireguardPeer Wireguard peer entity
	 */
	private function createPeer(stdClass $peer, WireguardInterface $interface): WireguardPeer {
		$ifPeer = new WireguardPeer($peer->publicKey, $peer->psk ?? null, $peer->keepalive, $peer->endpoint, $peer->port, $interface, new ArrayCollection([]));
		$addresses = $this->createPeerAddresses($peer->allowedIPs->ipv4, $ifPeer);
		$addresses = array_merge($addresses, $this->createPeerAddresses($peer->allowedIPs->ipv6, $ifPeer));
		$ifPeer->setAddresses(new ArrayCollection($addresses));
		return $ifPeer;
	}

	/**
	 * Creates array of wireguard peer addresses for new wireguard peer entity
	 * @param array<int, stdClass> $addrs Wireguard peer addresses
	 * @param WireguardPeer $ifPeer Wireguard peer entity
	 * @return array<int, WireguardPeerAddress> Wireguard peer addresses
	 */
	private function createPeerAddresses(array $addrs, WireguardPeer $ifPeer): array {
		$addresses = [];
		foreach ($addrs as $ip) {
			$addresses[] = new WireguardPeerAddress(Multi::factory($ip->address), $ip->prefix, $ifPeer);
		}
		return $addresses;
	}

	/**
	 * Creates array of wireguard peer addresses to update existing peer entity
	 * @param array<int, stdClass> $addrs Wireguard peer addresses
	 * @param WireguardPeer $ifPeer Wireguard peer entity
	 * @return array<int, WireguardPeer> Wireguard peer addresses
	 */
	private function updatePeerAddresses(array $addrs, WireguardPeer $ifPeer): array {
		$addresses = [];
		foreach ($addrs as $ip) {
			if (isset($ip->id)) {
				$peerAddr = $this->wireguardPeerAddressRepository->find($ip->id);
				if ($peerAddr === null) {
					throw new NonexistentWireguardTunnelException('Wireguard peer address not found');
				}
				$peerAddr->setAddress(Multi::factory($ip->address));
				$peerAddr->setPrefix($ip->prefix);
				$addresses[] = $peerAddr;
			} else {
				$addresses[] = new WireguardPeerAddress(Multi::factory($ip->address), $ip->prefix, $ifPeer);
			}
		}
		return $addresses;
	}

	/**
	 * Removes an existing Wireguard interface
	 * @param int $id Wireguard interface id
	 */
	public function removeInterface(int $id): void {
		$interface = $this->wireguardInterfaceRepository->find($id);
		if ($interface === null) {
			throw new NonexistentWireguardTunnelException('Wireguard tunnel not found');
		}
		$this->entityManager->remove($interface);
		$this->entityManager->flush();
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
		$output = $this->commandManager->run('wg pubkey', true, $privateKey);
		if ($output->getExitCode() !== 0) {
			throw new WireguardKeyErrorException($output->getStderr());
		}
		return $output->getStdout();
	}

}
