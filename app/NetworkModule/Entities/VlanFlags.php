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

namespace App\NetworkModule\Entities;

use stdClass;

class VlanFlags implements INetworkManagerFlags {

	/**
	 * Constructor
	 * @param bool $reorderHeaders This interface should reorder outgoing packet headers to look more like a non-VLAN Ethernet interface.
	 * @param bool $gvrp This interface should use GVRP to register itself with its switch.
	 * @param bool $looseBinding This interface's operating state is tied to the underlying network interface but other details (like routing) are not.
	 * @param bool $mvrp This interface should use MVRP to register itself with its switch.
	 */
	public function __construct(
		private readonly bool $reorderHeaders,
		private readonly bool $gvrp,
		private readonly bool $looseBinding,
		private readonly bool $mvrp,
	) {
	}

	/**
	 * Deserializes VLAN flags from JSON
	 * @param stdClass $json JSON serialized VLAN flags
	 * @return self VLAN flags entity
	 */
	public static function jsonDeserialize(stdClass $json): self {
		return new self(
			$json->reorderHeaders,
			$json->gvrp,
			$json->looseBinding,
			$json->mvrp,
		);
	}

	/**
	 * Deserializes VLAN flags from nmcli VLAN flag value
	 * @param int $nmCli nmcli VLAN flag value
	 * @return self VLAN flags entity
	 */
	public static function nmCliDeserialize(int $nmCli): self {
		return new self(
			reorderHeaders: ($nmCli & 0x01) === 1,
			gvrp: (($nmCli & 0x02) >> 1) === 1,
			looseBinding: (($nmCli & 0x04) >> 2) === 1,
			mvrp: (($nmCli & 0x08) >> 3) === 1,
		);
	}

	/**
	 * Serializes VLAN flags into JSON
	 * @return array{reorderHeaders: bool, gvrp: bool, looseBinding: bool, mvrp: bool} JSON serialized VLAN flags
	 */
	public function jsonSerialize(): array {
		return [
			'reorderHeaders' => $this->reorderHeaders,
			'gvrp' => $this->gvrp,
			'looseBinding' => $this->looseBinding,
			'mvrp' => $this->mvrp,
		];
	}

	/**
	 * Serializes VLAN flags into integer
	 * @return int VLAN flags serialized as integer
	 */
	public function nmCliSerialize(): int {
		return ($this->reorderHeaders) | ($this->gvrp << 1) | ($this->looseBinding << 2) | ($this->mvrp << 3);
	}

}
