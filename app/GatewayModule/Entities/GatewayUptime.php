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

namespace App\GatewayModule\Entities;

use DateTime;
use JsonSerializable;

/**
 * Gateway uptime entity
 */
class GatewayUptime implements JsonSerializable {

	/**
	 * Constructor
	 * @param int $id ID
	 * @param DateTime $start Start time
	 * @param DateTime|null $shutdown Shutdown time
	 * @param int $running Run time in seconds
	 * @param int $sleeping Sleep time in seconds
	 * @param int $downtime Downtime in seconds
	 * @param bool $graceful Was the shutdown graceful?
	 * @param string $kernel Kernel version
	 */
	public function __construct(
		public readonly int $id,
		public readonly DateTime $start,
		public readonly ?DateTime $shutdown,
		public readonly int $running,
		public readonly int $sleeping,
		public readonly int $downtime,
		public readonly bool $graceful,
		public readonly string $kernel,
	) {
	}

	/**
	 * Creates entity from tuptime CSV record
	 * @param array<string, string> $input
	 * @return self Created entity
	 */
	public static function fromTuptime(array $input): self {
		return new self(
			id: (int) $input['No.'],
			start: new DateTime('@' . $input['Startup T.']),
			shutdown: $input['Shutdown T.'] === '' ? null : new DateTime('@' . $input['Shutdown T.']),
			running: (int) $input['Running'],
			sleeping: (int) $input['Sleeping'],
			downtime: (int) $input['Downtime'],
			graceful: $input['End'] === 'OK',
			kernel: $input['Kernel'],
		);
	}

	/**
	 * Serializes entity into JSON
	 * @return array{id: int, start: string, shutdown: string|null, running: int, sleeping: int, downtime: int, graceful: bool, kernel: string} JSON serialized entity
	 */
	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'start' => $this->start->format('c'),
			'shutdown' => $this->shutdown?->format('c'),
			'running' => $this->running,
			'sleeping' => $this->sleeping,
			'downtime' => $this->downtime,
			'graceful' => $this->graceful,
			'kernel' => $this->kernel,
		];
	}

}
