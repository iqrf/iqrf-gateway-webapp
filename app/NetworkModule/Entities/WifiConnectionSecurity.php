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

use App\NetworkModule\Enums\WifiKeyManagement;
use JsonSerializable;
use Nette\Utils\Strings;

/**
 * WiFi connection security entity
 */
final class WifiConnectionSecurity implements JsonSerializable {

	/**
	 * @var WifiKeyManagement Key management used for the connection
	 */
	private $keyManagement;

	/**
	 * @var string Pre-shared key
	 */
	private $psk;

	/**
	 * Constructor
	 * @param WifiKeyManagement $keyManagement Key management used for the connection
	 * @param string $psk Pre-shared key
	 */
	public function __construct(WifiKeyManagement $keyManagement, string $psk) {
		$this->keyManagement = $keyManagement;
		$this->psk = $psk;
	}

	/**
	 * Creates a new WiFI connection security entity from nmcli connection configuration
	 * @param string $nmCli nmcli connection configuration
	 * @return WifiConnectionSecurity WiFI connection security entity
	 */
	public static function fromNmCli(string $nmCli): self {
		$array = explode(PHP_EOL, Strings::trim($nmCli));
		foreach ($array as $i => $row) {
			$temp = explode(':', $row, 2);
			if (Strings::startsWith($temp[0], '802-11-wireless-security.')) {
				$key = Strings::replace($temp[0], '~802-11-wireless-security\.~', '');
				$array[$key] = $temp[1];
			}
			unset($array[$i]);
		}
		$keyManagement = WifiKeyManagement::fromScalar($array['key-mgmt']);
		return new static($keyManagement, $array['psk']);
	}


	/**
	 * Returns JSON serialized data
	 * @return array<string, string|array> JSON serialized data
	 */
	public function jsonSerialize(): array {
		return [
			'keyManagement' => $this->keyManagement->toScalar(),
			'psk' => $this->psk,
		];
	}

}
