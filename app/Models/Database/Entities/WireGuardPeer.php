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
use App\Models\Database\Repositories\WireGuardPeerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * WireGuard peer entity
 */
#[ORM\Entity(repositoryClass: WireGuardPeerRepository::class)]
#[ORM\Table(name: 'wireguard_peers')]
#[ORM\HasLifecycleCallbacks]
class WireGuardPeer implements JsonSerializable {

	use TId;

	/**
	 * @var Collection<int, WireGuardPeerAddress> Peer allowed IPs
	 */
	#[ORM\OneToMany(
		targetEntity: WireGuardPeerAddress::class,
		mappedBy: 'peer',
		cascade: ['persist'],
		orphanRemoval: true,
	)]
	public Collection $addresses;

	/**
	 * Constructor
	 * @param string $publicKey Peer public key
	 * @param string|null $psk Peer pre-shared key
	 * @param int $keepalive Peer keepalive interval
	 * @param string $endpoint Peer endpoint
	 * @param int $port Peer listen port
	 * @param WireGuardInterface $interface WireGuard interface
	 */
	public function __construct(
		#[ORM\Column(type: Types::STRING, length: 255, unique: true)]
		public string $publicKey,
		#[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
		public ?string $psk,
		#[ORM\Column(type: Types::INTEGER)]
		public int $keepalive,
		#[ORM\Column(type: Types::STRING, length: 255)]
		public string $endpoint,
		#[ORM\Column(type: Types::INTEGER)]
		public int $port,
		#[ORM\ManyToOne(targetEntity: WireGuardInterface::class, inversedBy: 'peers')]
		#[ORM\JoinColumn(name: 'interface_id', nullable: false)]
		public WireGuardInterface $interface,
	) {
		$this->addresses = new ArrayCollection();
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
		foreach ($this->addresses->toArray() as $addr) {
			if ($addr->getAddress()->getVersion() === 4) {
				$ipv4[] = $addr->jsonSerialize();
			} else {
				$ipv6[] = $addr->jsonSerialize();
			}
		}
		return [
			'id' => $this->getId(),
			'publicKey' => $this->publicKey,
			'psk' => $this->psk,
			'keepalive' => $this->keepalive,
			'endpoint' => $this->endpoint,
			'port' => $this->port,
			'allowedIPs' => [
				'ipv4' => $ipv4,
				'ipv6' => $ipv6,
			],
			'tunnelId' => $this->interface->getId(),
		];
	}

	/**
	 * Serializes WireGuard peer entity into wg utility command
	 */
	public function wgSerialize(): string {
		$args = [];
		$args['peer'] = $this->publicKey;
		$psk = $this->psk;
		if ($psk !== null && $psk !== '') {
			$args['preshared-key'] = $psk;
		}
		$args['endpoint'] = sprintf('%s:%u', $this->endpoint, $this->port);
		$args['persistent-keepalive'] = $this->keepalive;
		$args['allowed-ips'] = implode(',', array_map(
			static fn (WireGuardPeerAddress $addr): string => $addr->getAddress()->toString(),
			$this->addresses->toArray()
		));
		return implode(' ', array_map(
			static fn (string $key, string $value): string => sprintf('%s %s', escapeshellarg($key), escapeshellarg($value)),
			array_keys($args),
			$args
		));
	}

}
