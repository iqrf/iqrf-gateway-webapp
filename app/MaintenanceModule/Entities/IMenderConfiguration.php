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

namespace App\MaintenanceModule\Entities;

use JsonSerializable;

/**
 * Mender configuration interface
 */
interface IMenderConfiguration extends JsonSerializable {

	/**
	 * Deserializes Mender configuration
	 * @param int $version Mender major version
	 * @param array<string, mixed> $config Mender configuration
	 * @return self Mender configuration entity
	 */
	public static function configDeserialize(int $version, array $config): self;

	/**
	 * Deserializes JSON serialized Mender configuration
	 * @param array{config: array<string, mixed>, version: int} $json JSON serialized Mender configuration entity
	 * @return self Mender configuration entity
	 */
	public static function jsonDeserialize(array $json): self;

	/**
	 * Serializes Mender configuration
	 * @return array<string, mixed> Mender configuration
	 */
	public function configSerialize(): array;

	/**
	 * Serializes JSON serialized Mender configuration
	 * @return array{config: array<string, mixed>, version: int} JSON serialized Mender configuration entity
	 */
	public function jsonSerialize(): array;

}
