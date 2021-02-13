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
use App\NetworkModule\Entities\MultiAddress;
use App\NetworkModule\Exceptions\NonexistentWireguardTunnelException;
use App\NetworkModule\Exceptions\WireguardInvalidEndpointException;
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
	 * @param string $name WireGuard interface name
	 * @return bool Is WireGuard interface active?
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
		$ipv4 = $ipv6 = null;
		if (property_exists($values, 'ipv4')) {
			$ipv4 = MultiAddress::fromPrefix($values->ipv4 . '/' . $values->ipv4Prefix);
		}
		if (property_exists($values, 'ipv6')) {
			$ipv6 = MultiAddress::fromPrefix($values->ipv6 . '/' . $values->ipv6Prefix);
		}
		$interface = new WireguardInterface($values->name, $values->privateKey, $values->port ?? null, $ipv4, $ipv6);
		foreach ($values->peers as $peer) {
			$interface->addPeer($this->createPeer($peer, $interface));
		}
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
		$ipv4 = $ipv6 = null;
		if (property_exists($values, 'ipv4')) {
			$ipv4 = MultiAddress::fromPrefix($values->ipv4 . '/' . $values->ipv4Prefix);
		}
		if (property_exists($values, 'ipv6')) {
			$ipv6 = MultiAddress::fromPrefix($values->ipv6 . '/' . $values->ipv6Prefix);
		}
		$interface->setName($values->name);
		$interface->setPrivateKey($values->privateKey);
		$interface->setPort($values->port ?? null);
		$interface->setIpv4($ipv4);
		$interface->setIpv6($ipv6);
		$oldPeers = $interface->getPeers()->toArray();
		$peersIds = [];
		foreach ($values->peers as $peer) {
			if (property_exists($peer, 'id') && $peer->id !== null) {
				$ifPeer = $this->wireguardPeerRepository->find($peer->id);
				if (!($ifPeer instanceof WireguardPeer)) {
					throw new NonexistentWireguardTunnelException('Wireguard peer not found');
				}
				if (!((bool) ip2long($peer->endpoint))) {
					$this->validateEndpoint($peer->endpoint);
				}
				$addresses = $this->updatePeerAddresses($peer->allowedIPs->ipv4, $ifPeer);
				$addresses = array_merge($addresses, $this->updatePeerAddresses($peer->allowedIPs->ipv6, $ifPeer));
				$ifPeer->setPublicKey($peer->publicKey);
				$ifPeer->setPsk($peer->psk ?? null);
				$ifPeer->setKeepalive($peer->keepalive);
				$ifPeer->setEndpoint($peer->endpoint);
				$ifPeer->setPort($peer->port);
				$ifPeer->setAddresses(new ArrayCollection($addresses));
				$this->entityManager->persist($ifPeer);
				$peersIds[] = $ifPeer->getId();
			} else {
				$interface->addPeer($this->createPeer($peer, $interface));
			}
		}
		foreach ($oldPeers as $peer) {
			if (!in_array($peer->getId(), $peersIds, true)) {
				$interface->deletePeer($peer);
			}
		}
		$this->entityManager->persist($interface);
		$this->entityManager->flush();
	}

	/**
	 * Checks DNS records for specified endpoint
	 */
	private function validateEndpoint(string $endpoint): void {
		$matches = dns_get_record($endpoint, DNS_A + DNS_AAAA);
		if ($matches === false || count($matches) === 0) {
			throw new WireguardInvalidEndpointException('No DNS record found for ' . $endpoint);
		}
	}

	/**
	 * Creates a wireguard peer entity
	 * @param stdClass $peer Peer entity configuration
	 * @param WireguardInterface $interface Wireguard interface
	 * @return WireguardPeer Wireguard peer entity
	 */
	private function createPeer(stdClass $peer, WireguardInterface $interface): WireguardPeer {
		if (!((bool) ip2long($peer->endpoint))) {
			$this->validateEndpoint($peer->endpoint);
		}
		$ifPeer = new WireguardPeer($peer->publicKey, $peer->psk ?? null, $peer->keepalive, $peer->endpoint, $peer->port, $interface);
		$this->createPeerAddresses($peer->allowedIPs->ipv4, $ifPeer);
		$this->createPeerAddresses($peer->allowedIPs->ipv6, $ifPeer);
		return $ifPeer;
	}

	/**
	 * Creates array of wireguard peer addresses for new wireguard peer entity
	 * @param array<int, stdClass> $addrs Wireguard peer addresses
	 * @param WireguardPeer $ifPeer Wireguard peer entity
	 */
	private function createPeerAddresses(array $addrs, WireguardPeer $ifPeer): void {
		foreach ($addrs as $ip) {
			$address = new WireguardPeerAddress(Multi::factory($ip->address), $ip->prefix, $ifPeer);
			$ifPeer->addAddress($address);
		}
	}

	/**
	 * Creates array of wireguard peer addresses to update existing peer entity
	 * @param array<int, stdClass> $addrs Wireguard peer addresses
	 * @param WireguardPeer $ifPeer Wireguard peer entity
	 * @return array<int, WireguardPeerAddress> Wireguard peer addresses
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
