<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

namespace App\NetworkModule\Entities\WifiSecurity;

use App\NetworkModule\Entities\INetworkManagerEntity;
use App\NetworkModule\Entities\WifiConnectionSecurity;
use App\NetworkModule\Enums\WepKeyType;
use App\NetworkModule\Exceptions\InvalidWepKeyIndexException;
use App\NetworkModule\Utils\NmCliConnection;
use stdClass;

/**
 * WEP (Wired Equivalent Privacy) entity
 */
class Wep implements INetworkManagerEntity {

	/**
	 * @var array<string> WEP keys
	 */
	private readonly array $keys;

	/**
	 * Constructor
	 * @param WepKeyType $type WEP key type
	 * @param int $index WEP key index
	 * @param array<string> $keys WEP keys
	 */
	public function __construct(
		private readonly WepKeyType $type,
		private readonly int $index,
		array $keys,
	) {
		if ($index < 0 || $index > 3) {
			throw new InvalidWepKeyIndexException();
		}
		$this->keys = array_values($keys);
	}

	/**
	 * Deserializes WEP entity from JSON
	 * @param stdClass $json JSON serialized data
	 * @return INetworkManagerEntity WEP entity
	 */
	public static function jsonDeserialize(stdClass $json): INetworkManagerEntity {
		$type = WepKeyType::from($json->type);
		return new self($type, $json->index, $json->keys);
	}

	/**
	 * Deserializes WEP entity from nmcli configuration
	 * @param array<string, array<string, array<string>|string>> $nmCli nmcli configuration
	 * @return INetworkManagerEntity WEP entity
	 */
	public static function nmCliDeserialize(array $nmCli): INetworkManagerEntity {
		$array = $nmCli[WifiConnectionSecurity::NMCLI_PREFIX];
		$type = WepKeyType::from($array['wep-key-type']);
		$index = (int) $array['wep-tx-keyidx'];
		$keys = [
			$array['wep-key0'],
			$array['wep-key1'],
			$array['wep-key2'],
			$array['wep-key3'],
		];
		return new self($type, $index, $keys);
	}

	/**
	 * Serializes WEP entity into JSON
	 * @return array{type: string, index: int, keys: array<string>} JSON serialized data
	 */
	public function jsonSerialize(): array {
		return [
			'type' => $this->type->jsonSerialize(),
			'index' => $this->index,
			'keys' => $this->keys,
		];
	}

	/**
	 * Serializes WEP entity into nmcli configuration string
	 * @return string nmcli configuration
	 */
	public function nmCliSerialize(): string {
		$array = [
			'wep-key-type' => $this->type->value,
			'wep-tx-keyidx' => $this->index,
			'wep-key0' => $this->keys[0] ?? '',
			'wep-key1' => $this->keys[1] ?? '',
			'wep-key2' => $this->keys[2] ?? '',
			'wep-key3' => $this->keys[3] ?? '',
		];
		return NmCliConnection::encode($array, WifiConnectionSecurity::NMCLI_PREFIX);
	}

}
