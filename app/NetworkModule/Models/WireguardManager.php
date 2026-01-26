<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

use App\Models\Database\Entities\WireguardInterface;
use App\Models\Database\Entities\WireguardInterfaceIpv4;
use App\Models\Database\Entities\WireguardInterfaceIpv6;
use App\Models\Database\Entities\WireguardPeer;
use App\Models\Database\Entities\WireguardPeerAddress;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\WireguardInterfaceIpv4Repository;
use App\Models\Database\Repositories\WireguardInterfaceIpv6Repository;
use App\Models\Database\Repositories\WireguardInterfaceRepository;
use App\Models\Database\Repositories\WireguardPeerRepository;
use App\NetworkModule\Entities\MultiAddress;
use App\NetworkModule\Enums\WireguardIpStack;
use App\NetworkModule\Exceptions\InterfaceExistsException;
use App\NetworkModule\Exceptions\NonexistentWireguardPeerException;
use App\NetworkModule\Exceptions\NonexistentWireguardTunnelException;
use App\NetworkModule\Exceptions\WireguardInvalidEndpointException;
use App\NetworkModule\Exceptions\WireguardKeyErrorException;
use App\ServiceModule\Models\ServiceManager;
use Darsyn\IP\Version\Multi;
use Exception;
use Iqrf\CommandExecutor\CommandExecutor;
use Nette\Utils\FileSystem;
use stdClass;

/**
 * WireGuard VPN manager
 */
class WireguardManager {

	/**
	 * WireGuard temporary directory
	 */
	private const TMP_DIR = '/tmp/wireguard/';

	/**
	 * @var WireguardInterfaceIpv4Repository WireGuard interface IPv4 repository
	 */
	private readonly WireguardInterfaceIpv4Repository $interfaceIpv4Repository;

	/**
	 * @var WireguardInterfaceIpv6Repository WireGuard interface IPv6 repository
	 */
	private readonly WireguardInterfaceIpv6Repository $interfaceIpv6Repository;

	/**
	 * @var WireguardInterfaceRepository WireGuard interface repository
	 */
	private readonly WireguardInterfaceRepository $interfaceRepository;

	/**
	 * @var WireguardPeerRepository WireGuard peer repository
	 */
	private readonly WireguardPeerRepository $peerRepository;

	/**
	 * Constructor
	 * @param CommandExecutor $commandManager Command manager
	 * @param EntityManager $entityManager Entity manager
	 * @param ServiceManager $serviceManager Service manager
	 */
	public function __construct(
		private readonly CommandExecutor $commandManager,
		private readonly EntityManager $entityManager,
		private readonly ServiceManager $serviceManager,
	) {
		$this->interfaceIpv4Repository = $this->entityManager->getWireguardInterfaceIpv4Repository();
		$this->interfaceIpv6Repository = $this->entityManager->getWireguardInterfaceIpv6Repository();
		$this->interfaceRepository = $this->entityManager->getWireguardInterfaceRepository();
		$this->peerRepository = $this->entityManager->getWireguardPeerRepository();
	}

	public function getInterfaceIpStack(WireguardInterface $interface): WireguardIpStack {
		if ($interface->getIpv4() === null) {
			return WireguardIpStack::IPV6;
		}
		if ($interface->getIpv6() === null) {
			return WireguardIpStack::IPV4;
		}
		return WireguardIpStack::DUAL;
	}

	/**
	 * Returns list of existing WireGuard interfaces configurations
	 * @return array<int, array<string, WireguardIpStack|bool|int|string|null>> List of WireGuard interfaces
	 */
	public function listInterfaces(): array {
		return array_map(fn (WireguardInterface $interface): array => [
			'id' => $interface->getId(),
			'name' => $interface->getName(),
			'active' => $this->serviceManager->isActive('iqrf-gateway-webapp-wg@' . $interface->getName()),
			'enabled' => $this->serviceManager->isEnabled('iqrf-gateway-webapp-wg@' . $interface->getName()),
			'stack' => $this->getInterfaceIpStack($interface),
		], $this->interfaceRepository->findAll());
	}

	/**
	 * Returns configuration of WireGuard interface
	 * @param int $id WireGuard interface id
	 * @return WireguardInterface WireGuard interface configuration
	 */
	public function getInterface(int $id): WireguardInterface {
		$interface = $this->interfaceRepository->find($id);
		if ($interface === null) {
			throw new NonexistentWireguardTunnelException('WireGuard tunnel not found');
		}
		return $interface;
	}

	/**
	 * Adds a new WireGuard interface
	 * @param stdClass $values New WireGuard interface configuration
	 * @return WireguardInterface Newly created WireGuard interface
	 */
	public function createInterface(stdClass $values): WireguardInterface {
		if ($this->interfaceRepository->findInterfaceByName($values->name) instanceof WireguardInterface) {
			throw new InterfaceExistsException(sprintf('WireGuard tunnel %s already exists.', $values->name));
		}
		$interface = new WireguardInterface($values->name, $values->privateKey, $values->port ?? null);
		if (property_exists($values, 'ipv4')) {
			$ipv4 = new WireguardInterfaceIpv4(new MultiAddress(Multi::factory($values->ipv4->address), $values->ipv4->prefix), $interface);
			$interface->setIpv4($ipv4);
		}
		if (property_exists($values, 'ipv6')) {
			$ipv6 = new WireguardInterfaceIpv6(new MultiAddress(Multi::factory($values->ipv6->address), $values->ipv6->prefix), $interface);
			$interface->setIpv6($ipv6);
		}
		$this->entityManager->persist($interface);
		$this->entityManager->flush();
		return $interface;
	}

	/**
	 * Edits an existing WireGuard interface
	 * @param int $id WireGuard interface ID
	 * @param stdClass $values WireGuard interface configuration
	 * @return WireguardInterface Updated WireGuard interface
	 */
	public function editInterface(int $id, stdClass $values): WireguardInterface {
		$interface = $this->getInterface($id);
		$tunnels = $this->interfaceRepository->findBy(['name' => $values->name]);
		foreach ($tunnels as $tunnel) {
			if ($tunnel !== $interface) {
				throw new InterfaceExistsException(sprintf('WireGuard tunnel %s already exists.', $values->name));
			}
		}
		$interface->setName($values->name);
		if (property_exists($values, 'privateKey')) {
			$interface->setPrivateKey($values->privateKey);
		}
		$interface->setPort($values->port ?? null);
		if (property_exists($values, 'ipv4')) {
			$this->updateInterfaceAddress($values->ipv4, $interface, 4);
		} else {
			$interface->setIpv4();
		}
		if (property_exists($values, 'ipv6')) {
			$this->updateInterfaceAddress($values->ipv6, $interface, 6);
		} else {
			$interface->setIpv6();
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
			throw new WireguardInvalidEndpointException('No DNS record found for ' . $endpoint);
		}
	}

	/**
	 * Creates a WireGuard peer entity
	 * @param stdClass $peer Peer entity configuration
	 * @param WireguardInterface $interface WireGuard interface
	 * @return WireguardPeer WireGuard peer entity
	 */
	public function createPeer(stdClass $peer, WireguardInterface $interface): WireguardPeer {
		if (!((bool) ip2long($peer->endpoint)) && function_exists('dns_get_record')) {
			$this->validateEndpoint($peer->endpoint);
		}
		$ifPeer = new WireguardPeer(
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
	 * @param WireguardPeer $ifPeer WireGuard peer entity
	 */
	public function createPeerAddresses(array $addresses, WireguardPeer $ifPeer): void {
		foreach ($addresses as $ip) {
			$address = new WireguardPeerAddress(new MultiAddress(Multi::factory($ip->address), $ip->prefix), $ifPeer);
			$ifPeer->addAddress($address);
		}
	}

	/**
	 * Get WireGuard peer
	 * @param int $id Wireguard peer id
	 * @return WireguardPeer Peer with given id
	 */
	public function getPeer(int $id): WireguardPeer {
		$peer = $this->peerRepository->find($id);
		if (!($peer instanceof WireguardPeer)) {
			throw new NonexistentWireguardPeerException('WireGuard peer not found');
		}
		return $peer;
	}

	/**
	 * Get all WireGuard peers
	 * @return array{WireGuardPeer} all wireguard peers
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
	public function modifyPeer(stdClass $peer, bool $flush = true): WireguardPeer {
		if (!property_exists($peer, 'id') || $peer->id === null) {
			throw new NonexistentWireguardPeerException('Peer ID not specified!');
		}

		$ifPeer = $this->getPeer($peer->id);
		if (!((bool) ip2long($peer->endpoint)) && function_exists('dns_get_record')) {
			$this->validateEndpoint($peer->endpoint);
		}
		if (property_exists($peer, 'tunnelId') && $peer->tunnelId !== $ifPeer->getInterface()->getId()) {
			$tunnel = $this->getInterface($peer->tunnelId);
			$ifPeer->setInterface($tunnel);
		}
		$this->updatePeerAddresses($peer->allowedIPs->ipv4, $ifPeer, 4);
		$this->updatePeerAddresses($peer->allowedIPs->ipv6, $ifPeer, 6);
		$ifPeer->setPublicKey($peer->publicKey);
		$ifPeer->setPsk($peer->psk ?? null);
		$ifPeer->setKeepalive($peer->keepalive);
		$ifPeer->setEndpoint($peer->endpoint);
		$ifPeer->setPort($peer->port);
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
		$interface = $this->interfaceRepository->find($id);
		if ($interface === null) {
			throw new NonexistentWireguardTunnelException('WireGuard tunnel not found');
		}
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
		$output = $this->commandManager->run('umask 077 && wg genkey');
		if ($output->getExitCode() !== 0) {
			throw new WireguardKeyErrorException($output->getStderr());
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
			throw new WireguardKeyErrorException($output->getStderr());
		}
		return $output->getStdout();
	}

	/**
	 * Returns WireGuard tunnel state string
	 * @param WireguardInterface $tunnel WireGuard tunnel
	 * @return string Tunnel state string
	 */
	public function getTunnelState(WireguardInterface $tunnel): string {
		$command = $this->commandManager->run($tunnel->wgStatus(), true);
		return $command->getExitCode() === 0 ? 'active' : 'inactive';
	}

	/**
	 * Checks if WireGuard tunnel is active using the wg utility
	 * @param WireguardInterface $tunnel WireGuard tunnel
	 * @return bool Is WireGuard tunnel active?
	 */
	public function isTunnelActive(WireguardInterface $tunnel): bool {
		$command = $this->commandManager->run($tunnel->wgStatus(), true);
		return $command->getExitCode() === 0;
	}

	/**
	 * Removes a tunnel using the ip utility
	 * @param WireguardInterface $tunnel WireGuard tunnel
	 * @return bool Was WireGuard tunnel successfully removed?
	 */
	public function deleteTunnel(WireguardInterface $tunnel): bool {
		$command = $this->commandManager->run($tunnel->ipDelete(), true);
		return $command->getExitCode() === 0;
	}

	/**
	 * Configures WireGuard interface and peers
	 * @param WireguardInterface $iface WireGuard interface entity
	 */
	public function initializeTunnel(WireguardInterface $iface): void {
		$name = $iface->getName();
		$output = $this->commandManager->run('ip link add ' . escapeshellarg($name) . ' type wireguard', true);
		if ($output->getExitCode() !== 0) {
			throw new Exception(sprintf('Failed to create new interface: %s.', $output->getStderr()));
		}
		FileSystem::createDir(self::TMP_DIR, 0700);
		$privateKeyFile = self::TMP_DIR . $name . '.privatekey';
		FileSystem::write($privateKeyFile, $iface->getPrivateKey(), 0600);
		$iface->setPrivateKey($privateKeyFile);
		$this->setPeerPsk($iface->getPeers()->toArray());
		$output = $this->commandManager->run($iface->wgSerialize(), true);
		if ($output->getExitCode() !== 0) {
			throw new Exception(sprintf('Failed to set wg tunnel properties: %s.', $output->getStderr()));
		}
		FileSystem::delete(self::TMP_DIR);
		if ($iface->getIpv4() instanceof WireguardInterfaceIpv4) {
			$this->setTunnelIp($name, $iface->getIpv4()->toString(), 4);
		}
		if ($iface->getIpv6() instanceof WireguardInterfaceIpv6) {
			$this->setTunnelIp($name, $iface->getIpv6()->toString(), 6);
		}
		$output = $this->commandManager->run('ip link set mtu 1420 up dev ' . escapeshellarg($name), true);
		if ($output->getExitCode() !== 0) {
			throw new Exception(sprintf('Failed to set interface MTU: %s.', $output->getStderr()));
		}
		$this->setPeerRoutes($name, $iface->getPeers()->toArray());
	}

	/**
	 * Sets peer preshared-key
	 * @param array<WireguardPeer> $peers Interface peers
	 */
	public function setPeerPsk(array $peers): void {
		foreach ($peers as $peer) {
			$psk = $peer->getPsk();
			if ($psk !== null) {
				$pskFile = self::TMP_DIR . $peer->getPublicKey() . '.psk';
				FileSystem::write($pskFile, $psk);
				$peer->setPsk($pskFile);
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
	 * @param array<int, WireguardPeer> $peers Interface peers
	 */
	public function setPeerRoutes(string $name, array $peers): void {
		foreach ($peers as $peer) {
			$addresses = [];
			foreach ($peer->getAddresses()->toArray() as $addr) {
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
	 * @param WireguardInterface $interface WireGuard interface
	 * @param int $protocol IP version
	 */
	private function updateInterfaceAddress(stdClass $ip, WireguardInterface $interface, int $protocol): void {
		$ifIp = $protocol === 4 ? $interface->getIpv4() : $interface->getIpv6();
		if ($ifIp !== null) {
			$ifIp->setAddress(new MultiAddress(Multi::factory($ip->address), $ip->prefix));
			$this->entityManager->persist($ifIp);
		} else {
			$newAddress = new MultiAddress(Multi::factory($ip->address), $ip->prefix);
			if ($protocol === 4) {
				$newIp = new WireguardInterfaceIpv4($newAddress, $interface);
				$interface->setIpv4($newIp);
			} else {
				$newIp = new WireguardInterfaceIpv6($newAddress, $interface);
				$interface->setIpv6($newIp);
			}
		}
	}

	/**
	 * Adds new, updates existing and deletes missing WireGuard peer addresses
	 * @param array<int, stdClass> $addrs WireGuard peer addresses
	 * @param WireguardPeer $ifPeer WireGuard peer entity
	 * @param int $protocol IP version
	 */
	private function updatePeerAddresses(array $addrs, WireguardPeer $ifPeer, int $protocol): void {
		$oldAddrs = $ifPeer->getAddresses()->toArray();
		$addrIds = [];
		foreach ($addrs as $ip) {
			$ipAddr = new MultiAddress(Multi::factory($ip->address), $ip->prefix);
			$found = false;
			foreach ($oldAddrs as $addr) {
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
				$ifPeer->addAddress(new WireguardPeerAddress($ipAddr, $ifPeer));
			}
		}
		foreach ($oldAddrs as $addr) {
			if ($addr->getAddress()->getVersion() !== $protocol) {
				continue;
			}
			if (in_array($addr->getId(), $addrIds, true)) {
				continue;
			}
			$ifPeer->deleteAddress($addr);
		}
	}

}
