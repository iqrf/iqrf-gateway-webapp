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
use App\NetworkModule\Entities\MultiAddress;
use Darsyn\IP\Version\Multi as IP;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * Wireguard peer entity
 * @ORM\Entity(repositoryClass="App\Models\Database\Repositories\WireguardPeerAddressRepository")
 * @ORM\Table(name="`wireguard_peer_addresses`")
 * @ORM\HasLifecycleCallbacks()
 */
class WireguardPeerAddress implements JsonSerializable {

	use TId;

	/**
	 * @var IP Peer address
	 * @ORM\Column(type="ip")
	 */
	private IP $address;

	/**
	 * @var int Peer address prefix
	 * @ORM\Column(type="integer")
	 */
	private int $prefix;

	/**
	 * @var WireguardPeer Wireguard peer
	 * @ORM\ManyToOne(targetEntity="WireguardPeer", inversedBy="addresses")
	 * @ORM\JoinColumn(name="peer_id")
	 */
	private WireguardPeer $peer;

	/**
	 * Constructor
	 * @param MultiAddress $address Peer address
	 * @param WireguardPeer $peer Wireguard peer
	 */
	public function __construct(MultiAddress $address, WireguardPeer $peer) {
		$this->address = $address->getAddress();
		$this->prefix = $address->getPrefix();
		$this->peer = $peer;
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
	 * Returns Wireguard peer
	 * @return WireguardPeer Wireguard peer
	 */
	public function getPeer(): WireguardPeer {
		return $this->peer;
	}

	/**
	 * Sets Wireguard peer
	 * @param WireguardPeer $peer Wireguard peer
	 */
	public function setPeer(WireguardPeer $peer): void {
		$this->peer = $peer;
	}

	/**
	 * Serializes wireguard peer allowed IP into JSON
	 * @return array{id: int|null, address: string, prefix: int} JSON serialized wireguard peer allowed IPs
	 */
	public function jsonSerialize(): array {
		$address = $this->address->getVersion() === 4 ? $this->address->getDotAddress() : $this->address->getCompactedAddress();
		return [
			'id' => $this->getId(),
			'address' => $address,
			'prefix' => $this->prefix,
		];
	}

}
