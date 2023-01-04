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

use JsonSerializable;
use stdClass;

/**
 * Interface for Network manager entities
 */
interface INetworkManagerEntity extends JsonSerializable {

	/**
	 * Deserializes the entity from JSON configuration
	 * @param stdClass $json JSON configuration
	 * @return INetworkManagerEntity Entity
	 */
	public static function jsonDeserialize(stdClass $json): INetworkManagerEntity;

	/**
	 * Deserializes the entity from nmcli configuration
	 * @param array<string, array<string, array<string>|string>> $nmCli nmcli configuration
	 * @return INetworkManagerEntity Entity
	 */
	public static function nmCliDeserialize(array $nmCli): INetworkManagerEntity;

	/**
	 * Serializes the entity into nmcli configuration
	 * @return string Entity serialized into nmcli configuration
	 */
	public function nmCliSerialize(): string;

}
