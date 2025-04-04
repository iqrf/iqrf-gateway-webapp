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

namespace App\InstallModule\Entities;

use JsonSerializable;

/**
 * Dependency entity
 */
class Dependency implements JsonSerializable {

	/**
	 * Constructor
	 * @param string $command Command name
	 * @param bool $critical Is the command required?
	 * @param string $package Package name
	 * @param string|null $feature Feature name
	 */
	public function __construct(
		private readonly string $command,
		private readonly bool $critical,
		private readonly string $package,
		private readonly ?string $feature = null,
	) {
	}

	/**
	 * Returns command name
	 * @return string Command name
	 */
	public function getCommand(): string {
		return $this->command;
	}

	/**
	 * Returns command required status
	 * @return bool Is the command required?
	 */
	public function isCritical(): bool {
		return $this->critical;
	}

	/**
	 * Returns package name
	 * @return string Package name
	 */
	public function getPackage(): string {
		return $this->package;
	}

	/**
	 * Returns feature name
	 * @return string|null Feature name
	 */
	public function getFeature(): ?string {
		return $this->feature;
	}

	/**
	 * Returns command entity serialized into an array
	 * @return array{command: string, critical: bool, package: string, feature: string|null} Serialized command entity
	 */
	public function jsonSerialize(): array {
		return [
			'command' => $this->command,
			'critical' => $this->critical,
			'package' => $this->package,
			'feature' => $this->feature,
		];
	}

}
