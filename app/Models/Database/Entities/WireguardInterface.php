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

namespace App\Models\Database\Entities;

use App\Models\Database\Attributes\TId;
use App\NetworkModule\Entities\MultiAddress;
use Darsyn\IP\Version\Multi as IP;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * Wireguard interface entity
 * @ORM\Entity(repositoryClass="App\Models\Database\Repositories\WireguardInterfaceRepository")
 * @ORM\Table(name="`wireguard_interfaces`")
 * @ORM\HasLifecycleCallbacks()
 */
class WireguardInterface implements JsonSerializable {

	use TId;

	/**
	 * @var string Interface name
	 * @ORM\Column(type="string", length=255, nullable=false, unique=true)
	 */
	private $name;

	/**
	 * @var string Interface private key
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	private $privateKey;

	/**
	 * @var int|null Interface listen port
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $port;

	/**
	 * @var IP|null Interface IPv4 address
	 * @ORM\Column(type="ip", nullable=true)
	 */
	private $ipv4;

	/**
	 * @var int|null Interface IPv4 address prefix
	 * @ORM\Column(type="integer", name="ipv4_prefix", nullable=true)
	 */
	private $ipv4Prefix;

	/**
	 * @var IP|null Interface IPv6 address
	 * @ORM\Column(type="ip", nullable=true)
	 */
	private $ipv6;

	/**
	 * @var int|null Interface IPv6 address prefix
	 * @ORM\Column(type="integer", name="ipv6_prefix", nullable=true)
	 */
	private $ipv6Prefix;

	/**
	 * @var Collection Interface peer IDs
	 * @ORM\OneToMany(targetEntity="WireguardPeer", mappedBy="interface", cascade={"persist"}, orphanRemoval=true)
	 */
	private $peers;

	/**
	 * Constructor
	 * @param string $name Wireguard tunnel interface name
	 * @param string $privateKey Wireguard tunnel interface private key
	 * @param int|null $port Wireguard tunnel interface listen port
	 * @param MultiAddress|null $ipv4 Interface IPv4 address
	 * @param MultiAddress|null $ipv6 Interface IPv6 address
	 */
	public function __construct(string $name, string $privateKey, ?int $port, ?MultiAddress $ipv4, ?MultiAddress $ipv6) {
		$this->name = $name;
		$this->privateKey = $privateKey;
		$this->port = $port;
		$this->ipv4 = $ipv4 === null ? null : $ipv4->getAddress();
		$this->ipv4Prefix = $ipv4 === null ? null : $ipv4->getPrefix();
		$this->ipv6 = $ipv6 === null ? null : $ipv6->getAddress();
		$this->ipv6Prefix = $ipv6 === null ? null : $ipv6->getPrefix();
		$this->peers = new ArrayCollection();
	}

	/**
	 * Returns Interface name
	 * @return string Interface name
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Sets Interface name
	 * @param string $name Interface name
	 */
	public function setName(string $name): void {
		$this->name = $name;
	}

	/**
	 * Returns Interface private key
	 * @return string Interface private key
	 */
	public function getPrivateKey(): string {
		return $this->privateKey;
	}

	/**
	 * Sets Interface private key
	 * @param string $privateKey Interface private key
	 */
	public function setPrivateKey(string $privateKey): void {
		$this->privateKey = $privateKey;
	}

	/**
	 * Returns Interface listen port
	 * @return int|null Interface listen port
	 */
	public function getPort(): ?int {
		return $this->port;
	}

	/**
	 * Sets Interface listen port
	 * @param int|null $port Interface listen port
	 */
	public function setPort(?int $port = null): void {
		$this->port = $port;
	}

	/**
	 * Returns Interface IPv4 address
	 * @return MultiAddress|null Interface IPv4 address
	 */
	public function getIpv4(): ?MultiAddress {
		if ($this->ipv4 === null && $this->ipv4Prefix === null) {
			return $this->ipv4;
		}
		return new MultiAddress($this->ipv4, $this->ipv4Prefix);
	}

	/**
	 * Sets Interface IPv4 address
	 * @param MultiAddress|null $ipv4 Interface IPv4 address
	 */
	public function setIpv4(?MultiAddress $ipv4 = null): void {
		$this->ipv4 = $ipv4 === null ? null : $ipv4->getAddress();
		$this->ipv4Prefix = $ipv4 === null ? null : $ipv4->getPrefix();
	}

	/**
	 * Returns Interface IPv6 address
	 * @return MultiAddress|null Interface IPv6 address
	 */
	public function getIpv6(): ?MultiAddress {
		if ($this->ipv6 === null && $this->ipv6Prefix === null) {
			return $this->ipv6;
		}
		return new MultiAddress($this->ipv6, $this->ipv6Prefix);
	}

	/**
	 * Sets Interface IPv6 address
	 * @param MultiAddress|null $ipv6 Interface IPv6 address
	 */
	public function setIpv6(?MultiAddress $ipv6 = null): void {
		$this->ipv6 = $ipv6 === null ? null : $ipv6->getAddress();
		$this->ipv6Prefix = $ipv6 === null ? null : $ipv6->getPrefix();
	}

	/**
	 * Adds WireGuard peer
	 * @param WireguardPeer $peer WireGuard peer to add
	 */
	public function addPeer(WireguardPeer $peer): void {
		$this->peers->add($peer);
	}

	/**
	 * Deletes WireGuard peer
	 * @param WireguardPeer $peer WireGuard peer to delete
	 */
	public function deletePeer(WireguardPeer $peer): void {
		$this->peers->removeElement($peer);
	}

	/**
	 * Returns interface peers
	 * @return Collection<WireguardPeer> interface peers
	 */
	public function getPeers(): Collection {
		return $this->peers;
	}

	/**
	 * Sets interface peers
	 * @param Collection $peers interface peers
	 */
	public function setPeers(Collection $peers): void {
		$this->peers = $peers;
	}

	/**
	 * Serializes wireguard interface entity into JSON
	 * @return array<string, array<array<string, int|string|null>>|int|string|null> JSON serialized wireguard interface entity
	 */
	public function jsonSerialize(): array {
		$ipv4 = $this->getIpv4();
		$ipv6 = $this->getIpv6();
		return [
			'id' => $this->getId(),
			'name' => $this->getName(),
			'privateKey' => $this->getPrivateKey(),
			'port' => $this->getPort(),
			'ipv4' => $ipv4 === null ? null : $ipv4->getAddress()->getDotAddress(),
			'ipv4Prefix' => $ipv4 === null ? null : $ipv4->getPrefix(),
			'ipv6' => $ipv6 === null ? null : $ipv6->getAddress()->getCompactedAddress(),
			'ipv6Prefix' => $ipv6 === null ? null : $ipv6->getPrefix(),
			'peers' => array_map(function (WireguardPeer $peer): array {
				return $peer->jsonSerialize();
			}, $this->getPeers()->toArray()),
		];
	}

	/**
	 * Serializes wireguard interface entity into wg utility command
	 * @return string JSON serialized wireguard interface entity
	 */
	public function wgSerialize(): string {
		$command = 'wg set ' . $this->getName();
		$command .= sprintf(' private-key %s', $this->getPrivateKey());
		$port = $this->getPort();
		if ($port !== null) {
			$command .= sprintf(' listen-port %u', $port);
		}
		foreach ($this->getPeers()->toArray() as $peer) {
			$command .= sprintf(' %s', $peer->wgSerialize());
		}
		return $command;
	}

	/**
	 * Returns a command to delete interface using the IP utility
	 * @return string IP utility interface delete command
	 */
	public function ipDelete(): string {
		return 'ip link delete dev ' . $this->getName();
	}

	/**
	 * Returns a command to show status of interface using the WG utility
	 * @return string WG utility interface status command
	 */
	public function wgStatus(): string {
		return 'wg show ' . $this->getName();
	}

}
