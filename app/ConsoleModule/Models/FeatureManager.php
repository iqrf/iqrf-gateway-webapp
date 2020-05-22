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

use App\CoreModule\Models\FeatureManager as CoreFeatureManager;
use Nette\Localization\ITranslator;

/**
 * Webapp's optional feature manager
 */
class FeatureManager extends CoreFeatureManager {

	/**
	 * @var ITranslator Translator
	 */
	private $translator;

	/**
	 * Constructor
	 * @param string $path Path to the configuration file
	 * @param ITranslator $translator ITranslator
	 */
	public function __construct(string $path, ITranslator $translator) {
		parent::__construct($path);
		$this->translator = $translator;
	}
	/**
	 * Lists the optional features
	 * @return array<array<string>> Optional features
	 */
	public function list(): array {
		$features = [];
		foreach ($this->read() as $name => $configuration) {
			$statusStr = ($configuration['enabled'] ?? false) ? 'enabled' : 'disabled';
			$fullName = $this->translator->translate('console.features.' . $name);
			$features[] = [$fullName, $name, $statusStr];
		}
		return $features;
	}

}
