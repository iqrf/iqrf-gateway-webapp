<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

use App\NetworkModule\Utils\NmCliConnection;
use stdClass;

/**
 * Automatic connecting entity
 */
class AutoConnect implements INetworkManagerEntity {

	/**
	 * @var bool AutoConnect enablement
	 */
	private bool $enabled;

	/**
	 * @var int Connection priority
	 */
	private int $priority;

	/**
	 * @var int Connection retries
	 */
	private int $retries;

	/**
	 * Constructor
	 * @param bool $enabled Automatic connecting enablement
	 * @param int $priority Connection priority
	 * @param int $retries Connection retries
	 */
	public function __construct(bool $enabled, int $priority, int $retries) {
		$this->enabled = $enabled;
		$this->priority = $priority;
		$this->retries = $retries;
	}

	/**
	 * Deserializes the automatic connecting entity from JSON configuration
	 * @param stdClass $json JSON configuration
	 * @return self Automatic connection entity
	 */
	public static function jsonDeserialize(stdClass $json): INetworkManagerEntity {
		return new self($json->enabled, $json->priority, $json->retries);
	}

	/**
	 * Serializes the automatic connecting entity into JSON
	 * @return array{enabled: bool, priority: int, retries: int} JSON serialized entity
	 */
	public function jsonSerialize(): array {
		return [
			'enabled' => $this->enabled,
			'priority' => $this->priority,
			'retries' => $this->retries,
		];
	}

	/**
	 * Deserializes the automatic connecting entity from nmcli configuration
	 * @param array<string, array<string, array<string>|string>> $nmCli nmcli configuration
	 * @return self Automatic connection entity
	 */
	public static function nmCliDeserialize(array $nmCli): INetworkManagerEntity {
		$array = $nmCli[ConnectionDetail::NMCLI_PREFIX];
		$enabled = $array['autoconnect'] === 'yes';
		$priority = (int) $array['autoconnect-priority'];
		$retries = (int) $array['autoconnect-retries'];
		return new self($enabled, $priority, $retries);
	}

	/**
	 * Serializes the automatic connecting entity into nmcli configuration
	 * @return string Entity serialized into nmcli configuration
	 */
	public function nmCliSerialize(): string {
		$array = [
			'autoconnect' => $this->enabled,
			'autoconnect-priority' => $this->priority,
			'autoconnect-retries' => $this->retries,
		];
		return NmCliConnection::encode($array, ConnectionDetail::NMCLI_PREFIX);
	}

}
