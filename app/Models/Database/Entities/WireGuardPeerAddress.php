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
use App\Models\Database\Repositories\WireGuardPeerAddressRepository;
use App\NetworkModule\Entities\MultiAddress;
use Darsyn\IP\Version\Multi as IP;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * WireGuard peer entity
 */
#[ORM\Entity(repositoryClass: WireGuardPeerAddressRepository::class)]
#[ORM\Table(name: 'wireguard_peer_addresses')]
#[ORM\HasLifecycleCallbacks]
class WireGuardPeerAddress implements JsonSerializable {

	use TId;

	/**
	 * @var IP Peer address
	 */
	#[ORM\Column(type: 'ip')]
	private IP $address;

	/**
	 * @var int Peer address prefix
	 */
	#[ORM\Column(type: Types::INTEGER)]
	private int $prefix;

	/**
	 * Constructor
	 * @param MultiAddress $address Peer address
	 * @param WireGuardPeer $peer WireGuard peer
	 */
	public function __construct(
		MultiAddress $address,
		#[ORM\ManyToOne(targetEntity: WireGuardPeer::class, inversedBy: 'addresses')]
		#[ORM\JoinColumn(name: 'peer_id', nullable: false)]
		public WireGuardPeer $peer,
	) {
		$this->address = $address->getAddress();
		$this->prefix = $address->getPrefix();
	}

	/**
	 * Returns peer address
	 * @return MultiAddress Peer address
	 */
	public function getAddress(): MultiAddress {
		return new MultiAddress($this->address, $this->prefix);
	}

	/**
	 * Sets peer address
	 * @param MultiAddress $address Peer address
	 */
	public function setAddress(MultiAddress $address): void {
		$this->address = $address->getAddress();
		$this->prefix = $address->getPrefix();
	}

	/**
	 * Serializes WireGuard peer allowed IP into JSON
	 * @return array{
	 *     id: int|null,
	 *     address: string,
	 *     prefix: int,
	 * } JSON serialized WireGuard peer allowed IPs
	 */
	public function jsonSerialize(): array {
		$address = $this->address->getVersion() === 4 ? $this->address->getDotAddress() : $this->address->getCompactedAddress();
		return [
			'id' => $this->id,
			'address' => $address,
			'prefix' => $this->prefix,
		];
	}

}
