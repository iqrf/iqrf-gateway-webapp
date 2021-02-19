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
use App\Models\Database\Entities\WireguardInterfaceIpv4;
use App\Models\Database\Entities\WireguardInterfaceIpv6;
use App\Models\Database\Entities\WireguardPeer;
use App\Models\Database\Entities\WireguardPeerAddress;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\WireguardInterfaceIpv4Repository;
use App\Models\Database\Repositories\WireguardInterfaceIpv6Repository;
use App\Models\Database\Repositories\WireguardInterfaceRepository;
use App\Models\Database\Repositories\WireguardPeerAddressRepository;
use App\Models\Database\Repositories\WireguardPeerRepository;
use App\NetworkModule\Entities\MultiAddress;
use App\NetworkModule\Exceptions\NonexistentWireguardTunnelException;
use App\NetworkModule\Exceptions\WireguardInvalidEndpointException;
use App\NetworkModule\Exceptions\WireguardKeyErrorException;
use App\ServiceModule\Models\ServiceManager;
use Darsyn\IP\Version\Multi;
use Exception;
use Nette\Utils\FileSystem;
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
	 * Wireguard temporary directory
	 */
	private const TMP_DIR = '/tmp/wireguard/';

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var EntityManager Entity manager
	 */
	private $entityManager;

	/**
	 * @var ServiceManager Service manager
	 */
	private $serviceManager;

	/**
	 * @var WireguardInterfaceIpv4Repository Wireguard interface IPv4 repository
	 */
	private $wireguardInterfaceIpv4Repository;

	/**
	 * @var WireguardInterfaceIpv6Repository Wireguard interface IPv6 repository
	 */
	private $wireguardInterfaceIpv6Repository;

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
	 * @param ServiceManager $serviceManager Service manager
	 */
	public function __construct(CommandManager $commandManager, EntityManager $entityManager, ServiceManager $serviceManager) {
		$this->commandManager = $commandManager;
		$this->entityManager = $entityManager;
		$this->serviceManager = $serviceManager;
		$this->wireguardInterfaceIpv4Repository = $this->entityManager->getWireguardInterfaceIpv4Repository();
		$this->wireguardInterfaceIpv6Repository = $this->entityManager->getWireguardInterfaceIpv6Repository();
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
				'active' => $this->serviceManager->isActive('iqrf-gateway-webapp-wg@' . $interface->getName()),
				'enabled' => $this->serviceManager->isEnabled('iqrf-gateway-webapp-wg@' . $interface->getName()),
			];
		}
		return $array;
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
		$interface = new WireguardInterface($values->name, $values->privateKey, $values->port ?? null);
		if (property_exists($values, 'ipv4')) {
			$ipv4 = new WireguardInterfaceIpv4(new MultiAddress(Multi::factory($values->ipv4->address), $values->ipv4->prefix), $interface);
			$interface->setIpv4($ipv4);
		}
		if (property_exists($values, 'ipv6')) {
			$ipv6 = new WireguardInterfaceIpv6(new MultiAddress(Multi::factory($values->ipv6->address), $values->ipv6->prefix), $interface);
			$interface->setIpv6($ipv6);
		}
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
		$interface->setName($values->name);
		$interface->setPrivateKey($values->privateKey);
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
				$this->updatePeerAddresses($peer->allowedIPs->ipv4, $ifPeer, 4);
				$this->updatePeerAddresses($peer->allowedIPs->ipv6, $ifPeer, 6);
				$ifPeer->setPublicKey($peer->publicKey);
				$ifPeer->setPsk($peer->psk ?? null);
				$ifPeer->setKeepalive($peer->keepalive);
				$ifPeer->setEndpoint($peer->endpoint);
				$ifPeer->setPort($peer->port);
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
	 * Updates existing Wireguard interface IP address or assigns a new one
	 * @param stdClass $ip IP address object
	 * @param WireguardInterface $interface Wireguard interface
	 * @param int $protocol IP version
	 */
	private function updateInterfaceAddress(stdClass $ip, WireguardInterface $interface, int $protocol): void {
		$repository = $protocol === 4 ? $this->wireguardInterfaceIpv4Repository : $this->wireguardInterfaceIpv6Repository;
		if (property_exists($ip, 'id')) {
			$ifIp = $repository->find($ip->id);
			if (!($ifIp instanceof WireguardInterfaceIpv4) && !($ifIp instanceof WireguardInterfaceIpv6)) {
				throw new NonexistentWireguardTunnelException('Wireguard interface ip address not found.');
			}
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
	 * Checks DNS records for specified endpoint
	 */
	public function validateEndpoint(string $endpoint): void {
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
	public function createPeer(stdClass $peer, WireguardInterface $interface): WireguardPeer {
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
	public function createPeerAddresses(array $addrs, WireguardPeer $ifPeer): void {
		foreach ($addrs as $ip) {
			$address = new WireguardPeerAddress(new MultiAddress(Multi::factory($ip->address), $ip->prefix), $ifPeer);
			$ifPeer->addAddress($address);
		}
	}

	/**
	 * Adds new, updates existing and deletes missing wireguard peer addresses
	 * @param array<int, stdClass> $addrs Wireguard peer addresses
	 * @param WireguardPeer $ifPeer Wireguard peer entity
	 * @param int $protocol IP version
	 */
	private function updatePeerAddresses(array $addrs, WireguardPeer $ifPeer, int $protocol): void {
		$oldAddrs = $ifPeer->getAddresses()->toArray();
		$addrIds = [];
		foreach ($addrs as $ip) {
			if (isset($ip->id)) {
				$peerAddr = $this->wireguardPeerAddressRepository->find($ip->id);
				if ($peerAddr === null) {
					throw new NonexistentWireguardTunnelException('Wireguard peer address not found');
				}
				$peerAddr->setAddress(new MultiAddress(Multi::factory($ip->address), $ip->prefix));
				$this->entityManager->persist($peerAddr);
				$addrIds[] = $peerAddr->getId();
			} else {
				$ifPeer->addAddress(new WireguardPeerAddress(new MultiAddress(Multi::factory($ip->address), $ip->prefix), $ifPeer));
			}
		}
		foreach ($oldAddrs as $addr) {
			if (!in_array($addr->getId(), $addrIds, true) && $addr->getAddress()->getVersion() === $protocol) {
				$ifPeer->deleteAddress($addr);
			}
		}
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
		$output = $this->commandManager->run('wg pubkey', false, $privateKey);
		if ($output->getExitCode() !== 0) {
			throw new WireguardKeyErrorException($output->getStderr());
		}
		return $output->getStdout();
	}

	/**
	 * Returns Wireguard tunnel state string
	 * @param WireguardInterface $tunnel Wireguard tunnel
	 * @return string Tunnel state string
	 */
	public function getTunnelState(WireguardInterface $tunnel): string {
		$command = $this->commandManager->run($tunnel->wgStatus(), true);
		return $command->getExitCode() === 0 ? 'active' : 'inactive';
	}

	/**
	 * Checks if Wireguard tunnel is active using the wg utility
	 * @param WireguardInterface $tunnel Wireguard tunnel
	 * @return bool Is Wireguard tunnel active?
	 */
	public function isTunnelActive(WireguardInterface $tunnel): bool {
		$command = $this->commandManager->run($tunnel->wgStatus(), true);
		return $command->getExitCode() === 0;
	}

	/**
	 * Removes a tunnel using the ip utility
	 * @param WireguardInterface $tunnel Wireguard tunnel
	 * @return bool Was Wireguard tunnel successfully removed?
	 */
	public function deleteTunnel(WireguardInterface $tunnel): bool {
		$command = $this->commandManager->run($tunnel->ipDelete(), true);
		return $command->getExitCode() === 0;
	}

	/**
	 * Configures Wireguard interface and peers
	 * @param WireguardInterface $iface Wireguard interface entity
	 */
	public function initializeTunnel(WireguardInterface $iface): void {
		$name = $iface->getName();
		$output = $this->commandManager->run('ip link add ' . $name . ' type wireguard', true);
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
		if ($iface->getIpv4() !== null) {
			$this->setTunnelIp($name, $iface->getIpv4()->toString(), 4);
		}
		if ($iface->getIpv6() !== null) {
			$this->setTunnelIp($name, $iface->getIpv6()->toString(), 6);
		}
		$output = $this->commandManager->run('ip link set mtu 1420 up dev ' . $name, true);
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
		$command = sprintf('ip -%u address add %s dev %s', $protocol, $address, $name);
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
				$command = sprintf('ip -6 route add %s dev %s', $addr, $name);
				$output = $this->commandManager->run($command, true);
				if ($output->getExitCode() !== 0) {
					throw new Exception(sprintf('Failed to set IPv6 route: %s.', $output->getStderr()));
				}
			}
		}
	}

}
