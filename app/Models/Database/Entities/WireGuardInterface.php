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
use App\Models\Database\Repositories\WireGuardInterfaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * WireGuard interface entity
 */
#[ORM\Entity(repositoryClass: WireGuardInterfaceRepository::class)]
#[ORM\Table(name: 'wireguard_interfaces')]
#[ORM\HasLifecycleCallbacks]
class WireGuardInterface implements JsonSerializable {

	use TId;

	/**
	 * Prefix for interface identifier used in system commands.
	 */
	private const INTERFACE_PREFIX = 'wg_iqrf_';

	/**
	 * Regex for matching the identifier for verification and extraction of the ID from the identifier string.
	 */
	private const INTERFACE_IDENTIFIER_REGEX = '/^wg_iqrf_(\d+)$/';

	/**
	 * @var WireGuardInterfaceIpv4|null Interface IPv4 address
	 */
	#[ORM\OneToOne(
		targetEntity: WireGuardInterfaceIpv4::class,
		mappedBy: 'interface',
		cascade: ['persist'],
		orphanRemoval: true,
	)]
	public ?WireGuardInterfaceIpv4 $ipv4 = null;

	/**
	 * @var WireGuardInterfaceIpv6|null Interface IPv6 address
	 */
	#[ORM\OneToOne(
		targetEntity: WireGuardInterfaceIpv6::class,
		mappedBy: 'interface',
		cascade: ['persist'],
		orphanRemoval: true,
	)]
	public ?WireGuardInterfaceIpv6 $ipv6 = null;

	/**
	 * @var Collection<int, WireGuardPeer> Interface peer IDs
	 */
	#[ORM\OneToMany(
		targetEntity: WireGuardPeer::class,
		mappedBy: 'interface',
		cascade: ['persist'],
		orphanRemoval: true,
	)]
	public Collection $peers;

	/**
	 * Constructor
	 * @param string $name WireGuard tunnel interface name
	 * @param string $privateKey WireGuard tunnel interface private key
	 * @param int|null $port WireGuard tunnel interface listen port
	 */
	public function __construct(
		#[ORM\Column(type: Types::STRING, length: 255, unique: true)]
		public string $name,
		#[ORM\Column(type: Types::STRING, length: 255, unique: true)]
		public string $privateKey,
		#[ORM\Column(type: Types::INTEGER, nullable: true)]
		public ?int $port,
	) {
		$this->peers = new ArrayCollection();
	}

	/**
	 * Verifies an identifier format using verification regex.
	 * @param string $identifier Identifier to verify
	 * @return int|null Interface ID extracted from identifier if valid, otherwise null
	 */
	public static function verifyIdentifier(string $identifier): ?int {
		$regexResult = preg_match(self::INTERFACE_IDENTIFIER_REGEX, $identifier, $matches);
		if (!$regexResult) {
			return null;
		}
		return intval($matches[1]);
	}

	/**
	 * Returns WireGuard interface identifier used for identification in system commands.
	 * @return string Interface identifier
	 */
	public function getInterfaceIdentifier(): string {
		return self::INTERFACE_PREFIX . $this->getId();
	}

	/**
	 * Serializes WireGuard interface entity into JSON
	 * @return array{
	 *     id: int|null,
	 *     name: string,
	 *     port: int|null,
	 *     ipv4?: array{
	 *         id: int|null,
	 *         address: string,
	 *         prefix: int,
	 *     },
	 *     ipv6?: array{
	 *         id: int|null,
	 *         address: string,
	 *         prefix: int,
	 *     },
	 * } JSON serialized WireGuard interface entity
	 */
	public function jsonSerialize(): array {
		$array = [
			'id' => $this->id,
			'name' => $this->name,
			'port' => $this->port,
		];
		if ($this->ipv4 instanceof WireGuardInterfaceIpv4) {
			$array['ipv4'] = $this->ipv4->jsonSerialize();
		}
		if ($this->ipv6 instanceof WireGuardInterfaceIpv6) {
			$array['ipv6'] = $this->ipv6->jsonSerialize();
		}
		return $array;
	}

	/**
	 * Serializes WireGuard interface entity into wg utility command
	 * @return string JSON serialized WireGuard interface entity
	 */
	public function wgSerialize(): string {
		$command = 'wg set ' . escapeshellarg($this->getInterfaceIdentifier());
		$command .= sprintf(' \'private-key\' %s', escapeshellarg($this->privateKey));
		$port = $this->port;
		if ($port !== null) {
			$command .= sprintf(' \'listen-port\' %s', escapeshellarg((string) $port));
		}
		return $command . implode('', array_map(static fn (WireGuardPeer $peer): string => ' ' . $peer->wgSerialize(), $this->peers->toArray()));
	}

	/**
	 * Returns a command to delete the interface using the IP utility
	 * @return string IP utility interface delete command
	 */
	public function ipDelete(): string {
		return 'ip link delete dev ' . escapeshellarg($this->getInterfaceIdentifier());
	}

	/**
	 * Returns a command to show the status of the interface using the WG utility
	 * @return string WG utility interface status command
	 */
	public function wgStatus(): string {
		return 'wg show ' . escapeshellarg($this->getInterfaceIdentifier());
	}

}
