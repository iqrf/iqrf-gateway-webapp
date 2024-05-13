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

namespace App\ServiceModule\Entities;

use JsonSerializable;

/**
 * Service state entity
 */
class ServiceState implements JsonSerializable {

	/**
	 * Constructor
	 * @param string $name Service name
	 * @param bool|null $enabled Is service enabled?
	 * @param bool|null $active Is service active?
	 * @param string|null $status Service status
	 */
	public function __construct(
		public readonly string $name,
		public readonly bool|null $enabled,
		public readonly bool|null $active,
		public readonly string|null $status = null,
	) {
	}

	/**
	 * Serializes service state into JSON
	 * @return array{name: string, enabled: bool|null, active: bool|null, status: string|null} JSON serialized service state
	 */
	public function jsonSerialize(): array {
		return [
			'name' => $this->name,
			'enabled' => $this->enabled,
			'active' => $this->active,
			'status' => $this->status,
		];
	}

}
