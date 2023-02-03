<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
 * Wireguard interface address entity
 * @ORM\Entity(repositoryClass="App\Models\Database\Repositories\WireguardInterfaceIpv4Repository")
 * @ORM\Table(name="`wireguard_interface_ipv4s`")
 * @ORM\HasLifecycleCallbacks()
 */
class WireguardInterfaceIpv4 implements JsonSerializable {

	use TId;

	/**
	 * @var IP Interface address
	 * @ORM\Column(type="ip")
	 */
	private IP $address;

	/**
	 * @var int Interface address prefix
	 * @ORM\Column(type="integer")
	 */
	private int $prefix;

	/**
	 * @var WireguardInterface WireGuard interface
	 * @ORM\OneToOne(targetEntity="WireguardInterface", inversedBy="ipv4")
	 * @ORM\JoinColumn(name="interface_id")
	 */
	private WireguardInterface $interface;

	/**
	 * Constructor
	 * @param MultiAddress $address Interface address
	 * @param WireguardInterface $interface WireGuard interface
	 */
	public function __construct(MultiAddress $address, WireguardInterface $interface) {
		$this->address = $address->getAddress();
		$this->prefix = $address->getPrefix();
		$this->interface = $interface;
	}

	/**
	 * Returns Wireguard interface IPv4 address
	 * @return MultiAddress Wireguard interface IPv4 address
	 */
	public function getAddress(): MultiAddress {
		return new MultiAddress($this->address, $this->prefix);
	}

	/**
	 * Sets new Wireguard interface IPv4 address and prefix
	 * @param MultiAddress $address Wireguard interface IPv4 address
	 */
	public function setAddress(MultiAddress $address): void {
		$this->address = $address->getAddress();
		$this->prefix = $address->getPrefix();
	}

	/**
	 * Returns Wireguard interface this address belongs to
	 * @return WireguardInterface Wireguard interface
	 */
	public function getInterface(): WireguardInterface {
		return $this->interface;
	}

	/**
	 * Sets Wireguard interface reference
	 * @param WireguardInterface $interface Wireguard interface
	 */
	public function setInterface(WireguardInterface $interface): void {
		$this->interface = $interface;
	}

	/**
	 * Serializes Wireguard interface IPv4 address to JSON
	 * @return array{id: int|null, address: string, prefix: int} JSON serialized Wireguard interface IPv4 address
	 */
	public function jsonSerialize(): array {
		return [
			'id' => $this->getId(),
			'address' => $this->address->getDotAddress(),
			'prefix' => $this->prefix,
		];
	}

	/**
	 * Returns string representation of Wireguard interface IPv4 address
	 * @return string Wireguard interface IPv4 string
	 */
	public function toString(): string {
		return $this->getAddress()->toString();
	}

}
