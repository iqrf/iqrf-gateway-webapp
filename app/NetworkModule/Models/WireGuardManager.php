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

namespace App\NetworkModule\Models;

use App\Models\Database\Entities\WireGuardInterface;
use App\Models\Database\Entities\WireGuardInterfaceIpv4;
use App\Models\Database\Entities\WireGuardInterfaceIpv6;
use App\Models\Database\Entities\WireGuardPeer;
use App\Models\Database\Entities\WireGuardPeerAddress;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\WireGuardInterfaceRepository;
use App\Models\Database\Repositories\WireGuardPeerRepository;
use App\NetworkModule\Entities\MultiAddress;
use App\NetworkModule\Enums\WireGuardIpStack;
use App\NetworkModule\Exceptions\InterfaceExistsException;
use App\NetworkModule\Exceptions\NonexistentWireGuardPeerException;
use App\NetworkModule\Exceptions\NonexistentWireGuardTunnelException;
use App\NetworkModule\Exceptions\PeerExistsException;
use App\NetworkModule\Exceptions\WireGuardInvalidEndpointException;
use App\NetworkModule\Exceptions\WireGuardKeyErrorException;
use Exception;
use Iqrf\CommandExecutor\CommandExecutor;
use Iqrf\ServiceManager\IServiceManager;
use Nette\Utils\FileSystem;
use stdClass;

/**
 * WireGuard VPN manager
 */
class WireGuardManager {

	/**
	 * WireGuard temporary directory
	 */
	private const TMP_DIR = '/tmp/wireguard/';

	/**
	 * @var WireGuardInterfaceRepository WireGuard interface repository
	 */
	private readonly WireGuardInterfaceRepository $interfaceRepository;

	/**
	 * @var WireGuardPeerRepository WireGuard peer repository
	 */
	private readonly WireGuardPeerRepository $peerRepository;

	/**
	 * Constructor
	 * @param CommandExecutor $commandManager Command manager
	 * @param EntityManager $entityManager Entity manager
	 * @param IServiceManager $serviceManager Service manager
	 */
	public function __construct(
		private readonly CommandExecutor $commandManager,
		private readonly EntityManager $entityManager,
		private readonly IServiceManager $serviceManager,
	) {
		$this->interfaceRepository = $this->entityManager->getWireGuardInterfaceRepository();
		$this->peerRepository = $this->entityManager->getWireGuardPeerRepository();
	}

	/**
	 * Returns WireGuard interface IP stack type
	 * @param WireGuardInterface $interface WireGuard interface
	 * @return WireGuardIpStack WireGuard interface IP stack type
	 */
	public function getInterfaceIpStack(WireGuardInterface $interface): WireGuardIpStack {
		if ($interface->ipv4 === null) {
			return WireGuardIpStack::IPV6;
		}
		if ($interface->ipv6 === null) {
			return WireGuardIpStack::IPV4;
		}
		return WireGuardIpStack::DUAL;
	}

	/**
	 * Returns list of existing WireGuard interfaces configurations
	 * @return array<int, array{
	 *     id: int,
	 *     name: string,
	 *     active: bool,
	 *     enabled: bool,
	 *     stack: WireGuardIpStack,
	 * }> List of WireGuard interfaces
	 */
	public function listInterfaces(): array {
		return array_map(fn (WireGuardInterface $interface): array => [
			'id' => $interface->getId(),
			'name' => $interface->name,
			'active' => $this->serviceManager->isActive('iqrf-gateway-webapp-wg@' . $interface->getInterfaceIdentifier()),
			'enabled' => $this->serviceManager->isEnabled('iqrf-gateway-webapp-wg@' . $interface->getInterfaceIdentifier()),
			'stack' => $this->getInterfaceIpStack($interface),
		], $this->interfaceRepository->findAll());
	}

	/**
	 * Returns configuration of WireGuard interface
	 * @param int $id WireGuard interface id
	 * @return WireGuardInterface WireGuard interface configuration
	 */
	public function getInterface(int $id): WireGuardInterface {
		$interface = $this->interfaceRepository->find($id);
		if ($interface === null) {
			throw new NonexistentWireGuardTunnelException('WireGuard tunnel not found');
		}
		return $interface;
	}

	/**
	 * Adds a new WireGuard interface
	 * @param stdClass $values New WireGuard interface configuration
	 * @return WireGuardInterface Newly created WireGuard interface
	 */
	public function createInterface(stdClass $values): WireGuardInterface {
		if ($this->interfaceRepository->findInterfaceByName($values->name) instanceof WireGuardInterface) {
			throw new InterfaceExistsException(sprintf('WireGuard tunnel %s already exists.', $values->name));
		}
		if ($this->interfaceRepository->findOneBy(['privateKey' => $values->privateKey]) instanceof WireGuardInterface) {
			throw new InterfaceExistsException('WireGuard interface with given private key already exists!');
		}
		$interface = new WireGuardInterface($values->name, $values->privateKey, $values->port ?? null);
		if (property_exists($values, 'ipv4')) {
			$interface->ipv4 = new WireGuardInterfaceIpv4(
				MultiAddress::fromString($values->ipv4->address, $values->ipv4->prefix),
				$interface,
			);
		}
		if (property_exists($values, 'ipv6')) {
			$interface->ipv6 = new WireGuardInterfaceIpv6(
				MultiAddress::fromString($values->ipv6->address, $values->ipv6->prefix),
				$interface,
			);
		}
		$this->entityManager->persist($interface);
		$this->entityManager->flush();
		return $interface;
	}

	/**
	 * Edits an existing WireGuard interface
	 * @param int $id WireGuard interface ID
	 * @param stdClass $values WireGuard interface configuration
	 * @return WireGuardInterface Updated WireGuard interface
	 */
	public function editInterface(int $id, stdClass $values): WireGuardInterface {
		$interface = $this->getInterface($id);
		$tunnels = $this->interfaceRepository->findBy(['name' => $values->name]);
		foreach ($tunnels as $tunnel) {
			if ($tunnel !== $interface) {
				throw new InterfaceExistsException(sprintf('WireGuard tunnel %s already exists.', $values->name));
			}
		}
		$interface->name = $values->name;
		if (property_exists($values, 'privateKey') && $interface->privateKey !== $values->privateKey) {
			$existingInterface = $this->interfaceRepository->findOneBy(['privateKey' => $values->privateKey]);
			if ($existingInterface instanceof WireGuardInterface) {
				throw new InterfaceExistsException('WireGuard interface with given private key already exists!');
			}
			$interface->privateKey = $values->privateKey;
		}
		$interface->port = $values->port ?? null;
		if (property_exists($values, 'ipv4')) {
			$this->updateInterfaceAddress($values->ipv4, $interface, 4);
		} else {
			$interface->ipv4 = null;
		}
		if (property_exists($values, 'ipv6')) {
			$this->updateInterfaceAddress($values->ipv6, $interface, 6);
		} else {
			$interface->ipv6 = null;
		}
		$this->entityManager->persist($interface);
		$this->entityManager->flush();
		return $interface;
	}

	/**
	 * Checks DNS records for specified endpoint
	 */
	public function validateEndpoint(string $endpoint): void {
		$matches = dns_get_record($endpoint, DNS_A + DNS_AAAA);
		if ($matches === false || $matches === []) {
			throw new WireGuardInvalidEndpointException('No DNS record found for ' . $endpoint);
		}
	}

	/**
	 * Creates a WireGuard peer entity
	 * @param stdClass $peer Peer entity configuration
	 * @param WireGuardInterface $interface WireGuard interface
	 * @return WireGuardPeer WireGuard peer entity
	 */
	public function createPeer(stdClass $peer, WireGuardInterface $interface): WireGuardPeer {
		$existingPeer = $this->peerRepository->findOneBy(['publicKey' => $peer->publicKey]);
		if ($existingPeer instanceof WireGuardPeer) {
			throw new PeerExistsException('WireGuard peer with given public key already exists!');
		}
		if (!((bool) ip2long($peer->endpoint)) && function_exists('dns_get_record')) {
			$this->validateEndpoint($peer->endpoint);
		}
		$ifPeer = new WireGuardPeer(
			$peer->publicKey,
			$peer->psk ?? null,
			$peer->keepalive,
			$peer->endpoint,
			$peer->port,
			$interface
		);
		$this->createPeerAddresses($peer->allowedIPs->ipv4, $ifPeer);
		$this->createPeerAddresses($peer->allowedIPs->ipv6, $ifPeer);
		$this->entityManager->persist($ifPeer);
		$this->entityManager->flush();
		return $ifPeer;
	}

	/**
	 * Creates array of WireGuard peer addresses for new WireGuard peer entity
	 * @param array<int, stdClass> $addresses WireGuard peer addresses
	 * @param WireGuardPeer $ifPeer WireGuard peer entity
	 */
	public function createPeerAddresses(array $addresses, WireGuardPeer $ifPeer): void {
		foreach ($addresses as $ip) {
			$address = new WireGuardPeerAddress(MultiAddress::fromString($ip->address, $ip->prefix), $ifPeer);
			$ifPeer->addresses->add($address);
		}
	}

	/**
	 * Get WireGuard peer
	 * @param int $id WireGuard peer id
	 * @return WireGuardPeer Peer with given id
	 */
	public function getPeer(int $id): WireGuardPeer {
		$peer = $this->peerRepository->find($id);
		if (!($peer instanceof WireGuardPeer)) {
			throw new NonexistentWireGuardPeerException('WireGuard peer not found');
		}
		return $peer;
	}

	/**
	 * Get all WireGuard peers
	 * @return array{WireGuardPeer} all WireGuard peers
	 */
	public function getAllPeers(): array {
		return $this->peerRepository->findAll();
	}

	/**
	 * Modify existing WireGuard peer
	 * @param stdClass $peer Object with peer data.
	 * @param bool $flush Flush data to database when update is finished (default = true).
	 *                     Can disable flush when called from function that does it itself.
	 */
	public function modifyPeer(stdClass $peer, bool $flush = true): WireGuardPeer {
		if (!property_exists($peer, 'id') || $peer->id === null) {
			throw new NonexistentWireGuardPeerException('Peer ID not specified!');
		}

		$ifPeer = $this->getPeer($peer->id);
		if (!((bool) ip2long($peer->endpoint)) && function_exists('dns_get_record')) {
			$this->validateEndpoint($peer->endpoint);
		}
		if ($ifPeer->publicKey !== $peer->publicKey) {
			$existingPeer = $this->peerRepository->findOneBy(['publicKey' => $peer->publicKey]);
			if ($existingPeer instanceof WireGuardPeer) {
				throw new PeerExistsException('WireGuard peer with given public key already exists!');
			}
		}
		if (property_exists($peer, 'tunnelId') && $peer->tunnelId !== $ifPeer->interface->getId()) {
			$ifPeer->interface = $this->getInterface($peer->tunnelId);
		}
		$this->updatePeerAddresses($peer->allowedIPs->ipv4, $ifPeer, 4);
		$this->updatePeerAddresses($peer->allowedIPs->ipv6, $ifPeer, 6);
		$ifPeer->publicKey = $peer->publicKey;
		$ifPeer->psk = $peer->psk ?? null;
		$ifPeer->keepalive = $peer->keepalive;
		$ifPeer->endpoint = $peer->endpoint;
		$ifPeer->port = $peer->port;
		$this->entityManager->persist($ifPeer);

		if ($flush) {
			$this->entityManager->flush();
		}

		return $ifPeer;
	}

	/**
	 * Removes WireGuard peer
	 * @param int $id id of peer to remove
	 */
	public function removePeer(int $id): void {
		$peer = $this->getPeer($id);
		$this->entityManager->remove($peer);
		$this->entityManager->flush();
	}

	/**
	 * Removes an existing WireGuard interface
	 * @param int $id WireGuard interface id
	 */
	public function removeInterface(int $id): void {
		$interface = $this->getInterface($id);
		$this->entityManager->remove($interface);
		$this->entityManager->flush();
	}

	/**
	 * Generates WireGuard keypair
	 * @return array{privateKey: string, publicKey: string} New key pair
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
	 * Generates WireGuard private key
	 * @return string WireGuard private key
	 */
	public function generatePrivateKey(): string {
		$output = $this->commandManager->run('wg genkey');
		if ($output->getExitCode() !== 0) {
			throw new WireGuardKeyErrorException($output->getStderr());
		}
		return $output->getStdout();
	}

	/**
	 * Derives WireGuard public key from private key
	 * @param string $privateKey Private key to derive public key from
	 * @return string WireGuard public key
	 */
	public function generatePublicKey(string $privateKey): string {
		$output = $this->commandManager->run('wg pubkey', false, 60, $privateKey);
		if ($output->getExitCode() !== 0) {
			throw new WireGuardKeyErrorException($output->getStderr());
		}
		return $output->getStdout();
	}

	/**
	 * Returns WireGuard tunnel state string
	 * @param WireGuardInterface $tunnel WireGuard tunnel
	 * @return string Tunnel state string
	 */
	public function getTunnelState(WireGuardInterface $tunnel): string {
		$command = $this->commandManager->run($tunnel->wgStatus(), true);
		return $command->getExitCode() === 0 ? 'active' : 'inactive';
	}

	/**
	 * Checks if WireGuard tunnel is active using the wg utility
	 * @param WireGuardInterface $tunnel WireGuard tunnel
	 * @return bool Is WireGuard tunnel active?
	 */
	public function isTunnelActive(WireGuardInterface $tunnel): bool {
		$command = $this->commandManager->run($tunnel->wgStatus(), true);
		return $command->getExitCode() === 0;
	}

	/**
	 * Removes a tunnel using the ip utility
	 * @param WireGuardInterface $tunnel WireGuard tunnel
	 * @return bool Was WireGuard tunnel successfully removed?
	 */
	public function deleteTunnel(WireGuardInterface $tunnel): bool {
		$command = $this->commandManager->run($tunnel->ipDelete(), true);
		return $command->getExitCode() === 0;
	}

	/**
	 * Configures WireGuard interface and peers
	 * @param WireGuardInterface $interface WireGuard interface entity
	 */
	public function initializeTunnel(WireGuardInterface $interface): void {
		$name = $interface->getInterfaceIdentifier();
		$output = $this->commandManager->run('ip link add ' . escapeshellarg($name) . ' type wireguard', true);
		if ($output->getExitCode() !== 0) {
			throw new Exception(sprintf('Failed to create new interface: %s.', $output->getStderr()));
		}
		FileSystem::createDir(self::TMP_DIR, 0700);
		$privateKeyFile = self::TMP_DIR . $name . '.privatekey';
		FileSystem::write($privateKeyFile, $interface->privateKey, 0600);
		$interface->privateKey = $privateKeyFile;
		$this->setPeerPsk($interface->peers->toArray());
		$output = $this->commandManager->run($interface->wgSerialize(), true);
		if ($output->getExitCode() !== 0) {
			throw new Exception(sprintf('Failed to set WireGuard tunnel properties: %s.', $output->getStderr()));
		}
		FileSystem::delete(self::TMP_DIR);
		if ($interface->ipv4 instanceof WireGuardInterfaceIpv4) {
			$this->setTunnelIp($name, $interface->ipv4->toString(), 4);
		}
		if ($interface->ipv6 instanceof WireGuardInterfaceIpv6) {
			$this->setTunnelIp($name, $interface->ipv6->toString(), 6);
		}
		$output = $this->commandManager->run('ip link set mtu 1420 up dev ' . escapeshellarg($name), true);
		if ($output->getExitCode() !== 0) {
			throw new Exception(sprintf('Failed to set interface MTU: %s.', $output->getStderr()));
		}
		$this->setPeerRoutes($name, $interface->peers->toArray());
	}

	/**
	 * Sets peer preshared-key
	 * @param array<WireGuardPeer> $peers Interface peers
	 */
	public function setPeerPsk(array $peers): void {
		foreach ($peers as $peer) {
			$psk = $peer->psk;
			if ($psk !== null) {
				$pskFile = self::TMP_DIR . $peer->publicKey . '.psk';
				FileSystem::write($pskFile, $psk);
				$peer->psk = $pskFile;
			}
		}
	}

	/**
	 * Sets tunnel IP address
	 * @param string $name Tunnel name
	 * @param string $address Interface IP address
	 * @param int $protocol IP address version
	 */
	public function setTunnelIp(string $name, string $address, int $protocol): void {
		$command = sprintf('ip -%u address add %s dev %s', $protocol, $address, escapeshellarg($name));
		$output = $this->commandManager->run($command, true);
		if ($output->getExitCode() !== 0) {
			throw new Exception(sprintf('Failed to set interface IPv%u address: %s.', $protocol, $output->getStderr()));
		}
	}

	/**
	 * Sets peer routes
	 * @param string $name Tunnel name
	 * @param array<int, WireGuardPeer> $peers Interface peers
	 */
	public function setPeerRoutes(string $name, array $peers): void {
		foreach ($peers as $peer) {
			$addresses = [];
			foreach ($peer->addresses->toArray() as $addr) {
				if ($addr->getAddress()->getVersion() === 6) {
					$addresses[] = $addr->getAddress()->toString();
				}
			}
			foreach ($addresses as $addr) {
				$command = sprintf('ip -6 route add %s dev %s', $addr, escapeshellarg($name));
				$output = $this->commandManager->run($command, true);
				if ($output->getExitCode() !== 0) {
					throw new Exception(sprintf('Failed to set IPv6 route: %s.', $output->getStderr()));
				}
			}
		}
	}

	/**
	 * Updates existing WireGuard interface IP address or assigns a new one
	 * @param stdClass $ip IP address object
	 * @param WireGuardInterface $interface WireGuard interface
	 * @param int $protocol IP version
	 */
	private function updateInterfaceAddress(stdClass $ip, WireGuardInterface $interface, int $protocol): void {
		$ifIp = $protocol === 4 ? $interface->ipv4 : $interface->ipv6;
		if ($ifIp !== null) {
			$ifIp->setAddress(MultiAddress::fromString($ip->address, $ip->prefix));
			$this->entityManager->persist($ifIp);
		} else {
			$newAddress = MultiAddress::fromString($ip->address, $ip->prefix);
			if ($protocol === 4) {
				$interface->ipv4 = new WireGuardInterfaceIpv4($newAddress, $interface);
			} else {
				$interface->ipv6 = new WireGuardInterfaceIpv6($newAddress, $interface);
			}
		}
	}

	/**
	 * Adds new, updates existing and deletes missing WireGuard peer addresses
	 * @param array<int, stdClass> $addresses WireGuard peer addresses
	 * @param WireGuardPeer $ifPeer WireGuard peer entity
	 * @param int $protocol IP version
	 */
	private function updatePeerAddresses(array $addresses, WireGuardPeer $ifPeer, int $protocol): void {
		$oldAddresses = $ifPeer->addresses->toArray();
		$addrIds = [];
		foreach ($addresses as $ip) {
			$ipAddr = MultiAddress::fromString($ip->address, $ip->prefix);
			$found = false;
			foreach ($oldAddresses as $addr) {
				$oldAddr = $addr->getAddress();
				if ($oldAddr->getVersion() !== $protocol) {
					continue;
				}
				if ($oldAddr->toString() !== $ipAddr->toString()) {
					continue;
				}
				$found = true;
				$addrIds[] = $addr->getId();
				break;
			}
			if (!$found) {
				$ifPeer->addresses->add(new WireGuardPeerAddress($ipAddr, $ifPeer));
			}
		}
		foreach ($oldAddresses as $addr) {
			if ($addr->getAddress()->getVersion() !== $protocol) {
				continue;
			}
			if (in_array($addr->getId(), $addrIds, true)) {
				continue;
			}
			$ifPeer->addresses->removeElement($addr);
		}
	}

}
