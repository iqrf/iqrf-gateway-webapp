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
	 * @var int Interface listen port
	 * @ORM\Column(type="integer", nullable=false)
	 */
	private $port;

	/**
	 * @var IP Interface IPv4 address
	 * @ORM\Column(type="ip", nullable=false)
	 */
	private $ipv4;

	/**
	 * @var int Interface IPv4 address prefix
	 * @ORM\Column(type="integer", name="ipv4_prefix", nullable=false)
	 */
	private $ipv4Prefix;

	/**
	 * @var IP Interface IPv6 address
	 * @ORM\Column(type="ip", nullable=false)
	 */
	private $ipv6;

	/**
	 * @var int Interface IPv6 address prefix
	 * @ORM\Column(type="integer", name="ipv6_prefix", nullable=false)
	 */
	private $ipv6Prefix;

	/**
	 * @var Collection Interface peer IDs
	 * @ORM\OneToMany(targetEntity="WireguardPeer", mappedBy="interface", cascade={"persist", "remove"})
	 */
	private $peers;

	/**
	 * Constructor
	 * @param string $name Wireguard tunnel interface name
	 * @param string $privateKey Wireguard tunnel interface private key
	 * @param int $port Wireguard tunnel interface listen port
	 * @param IP $ipv4 Interface IPv4 address
	 * @param int $ipv4Prefix Interface IPv4 address prefix
	 * @param IP $ipv6 Interface IPv6 address
	 * @param int $ipv6Prefix Interface IPv6 address prefix
	 * @param Collection $peers Interface peers
	 */
	public function __construct(string $name, string $privateKey, int $port, IP $ipv4, int $ipv4Prefix, IP $ipv6, int $ipv6Prefix, Collection $peers) {
		$this->name = $name;
		$this->privateKey = $privateKey;
		$this->port = $port;
		$this->ipv4 = $ipv4;
		$this->ipv4Prefix = $ipv4Prefix;
		$this->ipv6 = $ipv6;
		$this->ipv6Prefix = $ipv6Prefix;
		$this->peers = $peers;
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
	 * @return int Interface listen port
	 */
	public function getPort(): int {
		return $this->port;
	}

	/**
	 * Sets Interface listen port
	 * @param int $port Interface listen port
	 */
	public function setPort(int $port): void {
		$this->port = $port;
	}

	/**
	 * Returns Interface IPv4 address
	 * @return IP Interface IPv4 address
	 */
	public function getIpv4(): IP {
		return $this->ipv4;
	}

	/**
	 * Sets Interface IPv4 address
	 * @param IP $ipv4 Interface IPv4 address
	 */
	public function setIpv4(IP $ipv4): void {
		$this->ipv4 = $ipv4;
	}

	/**
	 * Returns Interface IPv4 address prefix
	 * @return int Interface IPv4 address prefix
	 */
	public function getIpv4Prefix(): int {
		return $this->ipv4Prefix;
	}

	/**
	 * Sets Interface IPv4 address prefix
	 * @param int $ipv4Prefix Interface IPv4 address prefix
	 */
	public function setIpv4Prefix(int $ipv4Prefix): void {
		$this->ipv4Prefix = $ipv4Prefix;
	}

	/**
	 * Returns Interface IPv6 address
	 * @return IP Interface IPv6 address
	 */
	public function getIpv6(): IP {
		return $this->ipv6;
	}

	/**
	 * Sets Interface IPv6 address
	 * @param IP $ipv6 Interface IPv6 address
	 */
	public function setIpv6(IP $ipv6): void {
		$this->ipv6 = $ipv6;
	}

	/**
	 * Returns Interface IPv6 address prefix
	 * @return int Interface IPv6 address prefix
	 */
	public function getIpv6Prefix(): int {
		return $this->ipv6Prefix;
	}

	/**
	 * Sets Interface IPv6 address prefix
	 * @param int $ipv6Prefix Interface IPv6 address prefix
	 */
	public function setIpv6Prefix(int $ipv6Prefix): void {
		$this->ipv6Prefix = $ipv6Prefix;
	}

	/**
	 * Returns interface peers
	 * @return Collection interface peers
	 */
	public function getPeers(): Collection {
		return $this->peers;
	}

	/**
	 * Sets interface peers
	 * @param ArrayCollection $peers interface peers
	 */
	public function setPeers(ArrayCollection $peers): void {
		$this->peers = $peers;
	}

	/**
	 * Serializes wireguard interface entity into JSON
	 * @return array<string, string|int> JSON serialized wireguard interface entity
	 */
	public function jsonSerialize(): array {
		return [
			'id' => $this->getId(),
			'name' => $this->getName(),
			'privateKey' => $this->getPrivateKey(),
			'port' => $this->getPort(),
			'ipv4' => $this->getIpv4()->getDotAddress(),
			'ipv4Prefix' => $this->getIpv4Prefix(),
			'ipv6' => $this->getIpv6()->getCompactedAddress(),
			'ipv6Prefix' => $this->getIpv6Prefix(),
		];
	}

}
