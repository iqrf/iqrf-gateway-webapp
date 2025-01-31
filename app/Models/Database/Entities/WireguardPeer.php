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

namespace App\Models\Database\Entities;

use App\Models\Database\Attributes\TId;
use App\Models\Database\Repositories\WireguardPeerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * WireGuard peer entity
 */
#[ORM\Entity(repositoryClass: WireguardPeerRepository::class)]
#[ORM\Table(name: 'wireguard_peers')]
#[ORM\HasLifecycleCallbacks]
class WireguardPeer implements JsonSerializable {

	use TId;

	/**
	 * @var Collection<int, WireguardPeerAddress> Peer allowed IPs
	 */
	#[ORM\OneToMany(mappedBy: 'peer', targetEntity: WireguardPeerAddress::class, cascade: ['persist'], orphanRemoval: true)]
	private Collection $addresses;

	/**
	 * Constructor
	 * @param string $publicKey Peer public key
	 * @param string|null $psk Peer pre-shared key
	 * @param int $keepalive Peer keepalive interval
	 * @param string $endpoint Peer endpoint
	 * @param int $port Peer listen port
	 * @param WireguardInterface $interface WireGuard interface
	 */
	public function __construct(
		#[ORM\Column(type: Types::STRING, length: 255)]
		private string $publicKey,
		#[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
		private ?string $psk,
		#[ORM\Column(type: Types::INTEGER)]
		private int $keepalive,
		#[ORM\Column(type: Types::STRING, length: 255)]
		private string $endpoint,
		#[ORM\Column(type: Types::INTEGER)]
		private int $port,
		#[ORM\ManyToOne(targetEntity: WireguardInterface::class, inversedBy: 'peers')]
		#[ORM\JoinColumn(name: 'interface_id', nullable: false)]
		private WireguardInterface $interface,
	) {
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
	 * Returns WireGuard interface
	 * @return WireguardInterface WireGuard interface
	 */
	public function getInterface(): WireguardInterface {
		return $this->interface;
	}

	/**
	 * Sets WireGuard interface
	 * @param WireguardInterface $interface WireGuard interface
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
	 * Deletes peer allowed IP address
	 * @param WireguardPeerAddress $address WireGuard allowed peer IP address
	 */
	public function deleteAddress(WireguardPeerAddress $address): void {
		$this->addresses->removeElement($address);
	}

	/**
	 * Returns peer allowed IPs
	 * @return Collection<int, WireguardPeerAddress> Peer allowed IPs
	 */
	public function getAddresses(): Collection {
		return $this->addresses;
	}

	/**
	 * Sets peer allowed IPs
	 * @param Collection<int, WireguardPeerAddress> $addresses Peer allowed IPs
	 */
	public function setAddresses(Collection $addresses): void {
		$this->addresses = $addresses;
	}

	/**
	 * Serializes WireGuard peer entity into JSON
	 * @return array{
	 *     id: int|null,
	 *     publicKey: string,
	 *     psk: string|null,
	 *     keepalive: int,
	 *     endpoint: string,
	 *     port: int,
	 *     allowedIPs: array{
	 *         ipv4: array<array{
	 *             id: int|null,
	 *             address: string,
	 *             prefix: int,
	 *         }>,
	 *         ipv6: array<array{
	 *             id: int|null,
	 *             address: string,
	 *             prefix: int,
	 *         }>,
	 *     },
	 * } JSON serialized WireGuard peer entity
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
	 * Serializes WireGuard peer entity into wg utility command
	 */
	public function wgSerialize(): string {
		$args = [];
		$args['peer'] = $this->getPublicKey();
		$psk = $this->getPsk();
		if ($psk !== null && $psk !== '') {
			$args['preshared-key'] = $psk;
		}
		$args['endpoint'] = sprintf('%s:%u', $this->getEndpoint(), $this->getPort());
		$args['persistent-keepalive'] = $this->getKeepalive();
		$args['allowed-ips'] = implode(',', array_map(
			static fn (WireguardPeerAddress $addr): string => $addr->getAddress()->toString(),
			$this->getAddresses()->toArray()
		));
		return implode(' ', array_map(
			static fn (string $key, string $value): string => sprintf('%s %s', escapeshellarg($key), escapeshellarg($value)),
			array_keys($args),
			$args
		));
	}

}
