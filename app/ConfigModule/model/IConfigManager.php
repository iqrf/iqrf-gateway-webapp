<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
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

namespace App\ConfigModule\Model;

/**
 * Configuration manager interface
 */
interface IConfigManager {

	/**
	 * Add a new configuration
	 * @param array $config Configuration to add
	 */
	public function add(array $config): void;

	/**
	 * Delete a configuration
	 * @param int $id Configuration ID
	 */
	public function delete(int $id): void;

	/**
	 * List component's configuration
	 * @return array Component's configuration
	 */
	public function list(): array;

	/**
	 * Load component's configuration
	 * @param int $id Configuration ID
	 * @return array Configuration
	 */
	public function load(int $id): array;

	/**
	 * Save the configuration
	 * @param array $config Configuration to save
	 * @param int $id Configuration ID
	 */
	public function save(array $config, int $id): void;
}
