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

namespace App\NetworkModule\Entities;

use JsonSerializable;
use stdClass;

/**
 * Wireguard peer configuration
 */
final class WireguardPeer implements JsonSerializable {

	/**
	 * @var string $publicKey Wireguard peer public key
	 */
	private $publicKey;

	/**
	 * @var string|null $psk Wireguard peer pre-shared key
	 */
	private $psk;

	/**
	 * @var int $keepalive Wireguard peer keepalive interval
	 */
	private $keepalive;

	/**
	 * @var string $endpoint Wireguard peer endpoint
	 */
	private $endpoint;

	/**
	 * @var int $port Wireguard peer listen port
	 */
	private $port;

	/**
	 * @var WireguardAllowedIPs $allowedIPs Wireguard peer allowed IPs
	 */
	private $allowedIPs;

	/**
	 * Constructor
	 * @param string $publicKey Wireguard peer public key
	 * @param string|null $psk Wireguard peer pre-shared key
	 * @param int $keepalive Wireguard peer keepalive interval
	 * @param string $endpoint Wireguard peer endpoint
	 * @param int $port Wireguard peer listen port
	 * @param WireguardAllowedIPs $allowedIPs Wireguard peer allowed IP addresses
	 */
	public function __construct(string $publicKey, ?string $psk, int $keepalive, string $endpoint, int $port, WireguardAllowedIPs $allowedIPs) {
		$this->publicKey = $publicKey;
		$this->psk = $psk;
		$this->keepalive = $keepalive;
		$this->endpoint = $endpoint;
		$this->port = $port;
		$this->allowedIPs = $allowedIPs;
	}

	/**
	 * Deserializes Wireguard peer configuration JSON intto entity
	 * @param stdClass $json Wireguard peer configuration JSON
	 */
	public static function jsonDeserialize(stdClass $json): self {
		$allowedIPs = WireguardAllowedIPs::jsonDeserialize($json->allowedIPs);
		return new self($json->publicKey, null, $json->keepalive, $json->endpoint, $json->port, $allowedIPs);
	}

	/**
	 * Serializes Wireguard peer configuration entity into JSON
	 * @return array<string, array<string, array<array<string, int|string>>>|int|string|null> JSON serialized Wireguard peer configuration entity
	 */
	public function jsonSerialize(): array {
		return [
			'publicKey' => $this->publicKey,
			'psk' => $this->psk,
			'keepalive' => $this->keepalive,
			'endpoint' => $this->endpoint,
			'port' => $this->port,
			'allowedIPs' => $this->allowedIPs->jsonSerialize(),
		];
	}

}
