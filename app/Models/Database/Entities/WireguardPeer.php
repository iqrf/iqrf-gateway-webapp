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
	 * @var int Interface this peer belongs to
	 * @ORM\ManyToOne(targetEntity="WireguardInterface", inversedBy="peers", cascade={"persist"})
	 * @ORM\JoinColumn(name="interface_id", referencedColumnName="id")
	 */
	private $interfaceId;

	/**
	 * Constructor
	 * @param string $publicKey Peer public key
	 * @param string|null $psk Peer pre-shared key
	 * @param int $keepalive Peer keepalive interval
	 * @param string $endpoint Peer endpoint
	 * @param int $port Peer listen port
	 * @param int|null $interfaceId Interface this peer belongs to
	 */
	public function __construct(string $publicKey, ?string $psk, int $keepalive, string $endpoint, int $port, ?int $interfaceId) {
		$this->publicKey = $publicKey;
		$this->psk = $psk;
		$this->keepalive = $keepalive;
		$this->endpoint = $endpoint;
		$this->port = $port;
		$this->interfaceId = $interfaceId;
	}

	/**
	 * Serializes wireguard peer entity into JSON
	 * @return array<string, string|int|null> JSON serialized wireguard peer entity
	 */
	public function jsonSerialize(): array {
		return [
			'id' => $this->getId(),
			'publicKey' => $this->publicKey,
			'psk' => $this->psk,
			'keepalive' => $this->keepalive,
			'endpoint' => $this->endpoint,
			'port' => $this->port,
			'interface' => $this->interfaceId,
		];
	}

}
