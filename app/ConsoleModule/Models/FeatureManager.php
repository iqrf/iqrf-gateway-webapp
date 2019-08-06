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

namespace App\ConsoleModule\Models;

use App\ConsoleModule\Exceptions\UnknownFeatureException;
use Kdyby\Translation\Translator;
use Nette\IOException;
use Nette\Neon\Exception as NeonException;
use Nette\Neon\Neon;
use Nette\Utils\FileSystem;

/**
 * Webapp's optional feature manager
 */
class FeatureManager {

	/**
	 * Path to the webapp's configuration file
	 */
	private const CONF_PATH = __DIR__ . '/../../config/features.neon';

	/**
	 * @var Translator Translator
	 */
	private $translator;

	/**
	 * Constructor
	 * @param Translator $translator Translator
	 */
	public function __construct(Translator $translator) {
		$this->translator = $translator;
	}

	/**
	 * Disables optional features
	 * @param string[] $names Names of disabled optional features
	 * @throws IOException
	 * @throws NeonException
	 * @throws UnknownFeatureException
	 */
	public function disable(array $names): void {
		$this->editConfig($names, false);
	}

	/**
	 * Edits the configuration of optional features
	 * @param string[] $names Names of edited optional features
	 * @param bool $state State of optional features
	 * @throws IOException
	 * @throws NeonException
	 * @throws UnknownFeatureException
	 */
	private function editConfig(array $names, bool $state): void {
		$config = $this->readConfig();
		foreach ($names as $name) {
			if (!array_key_exists($name, $config)) {
				throw new UnknownFeatureException($name);
			}
		}
		foreach ($names as $name) {
			$config[$name] = $state;
		}
		$this->saveConfig($config);
	}

	/**
	 * Reads the configuration of optional features
	 * @return array<string,bool> Optional feature configuration
	 * @throws NeonException
	 */
	private function readConfig(): array {
		try {
			$config = FileSystem::read(self::CONF_PATH);
		} catch (IOException $e) {
			return [];
		}
		return Neon::decode($config)['parameters']['features'] ?? [];
	}

	/**
	 * Saves the configuration of optional features
	 * @param array<string,bool> $config Optional feature configuration
	 * @throws IOException
	 * @throws NeonException
	 */
	private function saveConfig(array $config): void {
		$neon = ['parameters' => ['features' => $config]];
		$fileContent = Neon::encode($neon, Neon::BLOCK);
		FileSystem::write(self::CONF_PATH, $fileContent);
	}

	/**
	 * Enables optional features
	 * @param string[] $names Names of enabled optional features
	 * @throws IOException
	 * @throws NeonException
	 * @throws UnknownFeatureException
	 */
	public function enable(array $names): void {
		$this->editConfig($names, true);
	}

	/**
	 * Lists the optional features
	 * @return array<array<string,string,string>> Optional features
	 * @throws NeonException
	 */
	public function list(): array {
		$features = [];
		foreach ($this->readConfig() as $name => $status) {
			$statusStr = $status ? 'enabled' : 'disabled';
			$fullName = $this->translator->translate('console.features.' . $name);
			$features[] = [$fullName, $name, $statusStr];
		}
		return $features;
	}

}
