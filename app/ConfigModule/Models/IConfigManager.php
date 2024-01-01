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

namespace App\ConfigModule\Models;

/**
 * Configuration manager interface
 */
interface IConfigManager {

	/**
	 * Adds a new configuration
	 * @param array<mixed> $config Configuration to add
	 */
	public function add(array $config): void;

	/**
	 * Deletes a configuration
	 * @param int $id Configuration ID
	 */
	public function delete(int $id): void;

	/**
	 * Lists component's configuration
	 * @return array<mixed> Component's configuration
	 */
	public function list(): array;

	/**
	 * Loads the component's configuration
	 * @param int $id Configuration ID
	 * @return array<mixed> Configuration
	 */
	public function load(int $id): array;

	/**
	 * Saves the configuration
	 * @param array<mixed> $config Configuration to save
	 * @param int $id Configuration ID
	 */
	public function save(array $config, int $id): void;

}
