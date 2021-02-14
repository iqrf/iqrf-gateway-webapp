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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * Wireguard peer entity
 * @ORM\Entity(repositoryClass="App\Models\Database\Repositories\WireguardPeerRepository")
 * @ORM\Table(name="`wireguard_peers`")
 * @ORM\HasLifecycleCallbacks()
 */
class WireguardPeer implements JsonSerializable {

	use TId;

	/**
	 * @var string Peer public key
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	private $publicKey;

	/**
	 * @var string|null Peer pre-shared key
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $psk;

	/**
	 * @var int Peer keepalive interval
	 * @ORM\Column(type="integer", nullable=false)
	 */
	private $keepalive;

	/**
	 * @var string Peer endpoint
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	private $endpoint;

	/**
	 * @var int Peer listen port
	 * @ORM\Column(type="integer", nullable=false)
	 */
	private $port;

	/**
	 * @var WireguardInterface Interface
	 * @ORM\ManyToOne(targetEntity="WireguardInterface", inversedBy="peers")
	 * @ORM\JoinColumn(name="interface_id", referencedColumnName="id")
	 */
	private $interface;

	/**
	 * @var Collection<WireguardPeerAddress> Peer allowed IPs
	 * @ORM\OneToMany(targetEntity="WireguardPeerAddress", mappedBy="peer", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	private $addresses;

	/**
	 * Constructor
	 * @param string $publicKey Peer public key
	 * @param string|null $psk Peer pre-shared key
	 * @param int $keepalive Peer keepalive interval
	 * @param string $endpoint Peer endpoint
	 * @param int $port Peer listen port
	 * @param WireguardInterface $interface Wireguard interface
	 */
	public function __construct(string $publicKey, ?string $psk, int $keepalive, string $endpoint, int $port, WireguardInterface $interface) {
		$this->publicKey = $publicKey;
		$this->psk = $psk;
		$this->keepalive = $keepalive;
		$this->endpoint = $endpoint;
		$this->port = $port;
		$this->interface = $interface;
		$this->addresses = new ArrayCollection();
	}

	/**
	 * Returns peer public key
	 * @return string Peer public key
	 */
	public function getPublicKey(): string {
		return $this->publicKey;
	}

	/**
	 * Sets peer public key
	 * @param string $publicKey Peer public key
	 */
	public function setPublicKey(string $publicKey): void {
		$this->publicKey = $publicKey;
	}

	/**
	 * Returns peer pre-shared key
	 * @return string|null Peer pre-shared key
	 */
	public function getPsk(): ?string {
		return $this->psk;
	}

	/**
	 * Sets peer pre-shared key
	 * @param string|null $psk Peer pre-shared key
	 */
	public function setPsk(?string $psk = null): void {
		$this->psk = $psk;
	}

	/**
	 * Returns peer keepalive interval
	 * @return int Peer keepalive interval
	 */
	public function getKeepalive(): int {
		return $this->keepalive;
	}

	/**
	 * Sets peer keepalive interval
	 * @param int $keepalive Peer keepalive interval
	 */
	public function setKeepalive(int $keepalive): void {
		$this->keepalive = $keepalive;
	}

	/**
	 * Returns peer endpoint
	 * @return string Peer endpoint
	 */
	public function getEndpoint(): string {
		return $this->endpoint;
	}

	/**
	 * Sets peer endpoint
	 * @param string $endpoint Peer endpoint
	 */
	public function setEndpoint(string $endpoint): void {
		$this->endpoint = $endpoint;
	}

	/**
	 * Return peer listen port
	 * @return int Peer listen port
	 */
	public function getPort(): int {
		return $this->port;
	}

	/**
	 * Sets peer listen port
	 * @param int $port peer listen port
	 */
	public function setPort(int $port): void {
		$this->port = $port;
	}

	/**
	 * Returns Wireguard interface
	 * @return WireguardInterface Wireguard interface
	 */
	public function getInterface(): WireguardInterface {
		return $this->interface;
	}

	/**
	 * Sets Wireguard interface
	 * @param WireguardInterface $interface Wireguard interface
	 */
	public function setInterface(WireguardInterface $interface): void {
		$this->interface = $interface;
	}

	/**
	 * Adds peer allowed IP address
	 * @param WireguardPeerAddress $address WireGuard allowed peer IP address
	 */
	public function addAddress(WireguardPeerAddress $address): void {
		$this->addresses->add($address);
	}

	/**
	 * Returns peer allowed IPs
	 * @return Collection<WireguardPeerAddress> Peer allowed IPs
	 */
	public function getAddresses(): Collection {
		return $this->addresses;
	}

	/**
	 * Sets peer allowed IPs
	 * @param Collection<WireguardPeerAddress> $addresses Peer allowed IPs
	 */
	public function setAddresses(Collection $addresses): void {
		$this->addresses = $addresses;
	}

	/**
	 * Serializes wireguard peer entity into JSON
	 * @return array<string, array<string, array<int, mixed>>|int|string|null> JSON serialized wireguard peer entity
	 */
	public function jsonSerialize(): array {
		$ipv4 = $ipv6 = [];
		foreach ($this->getAddresses()->toArray() as $addr) {
			if ($addr->getAddress()->getVersion() === 4) {
				$ipv4[] = $addr->jsonSerialize();
			} else {
				$ipv6[] = $addr->jsonSerialize();
			}
		}
		return [
			'id' => $this->getId(),
			'publicKey' => $this->getPublicKey(),
			'psk' => $this->getPsk(),
			'keepalive' => $this->getKeepalive(),
			'endpoint' => $this->getEndpoint(),
			'port' => $this->getPort(),
			'allowedIPs' => [
				'ipv4' => $ipv4,
				'ipv6' => $ipv6,
			],
		];
	}

	/**
	 * Serializes wireguard peer entity into wg utility command
	 */
	public function wgSerialize(): string {
		$command = 'peer ' . $this->getPublicKey();
		$psk = $this->getPsk();
		if ($psk !== null && $psk !== '') {
			$command .= sprintf(' preshared-key %s', $psk);
		}
		$command .= sprintf(' endpoint %s:%u', $this->getEndpoint(), $this->getPort());
		$command .= sprintf(' persistent-keepalive %u', $this->getKeepalive());
		$command .= sprintf(
			' allowed-ips %s',
			implode(
				',',
				array_map(function (WireguardPeerAddress $addr): string {
					return $addr->getAddress()->toString();
				}, $this->getAddresses()->toArray())
			)
		);
		return $command;
	}

}
