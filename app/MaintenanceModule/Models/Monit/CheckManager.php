<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

namespace App\MaintenanceModule\Models\Monit;

use Nette\Schema\Elements\Type;
use Nette\Schema\Expect;
use Nette\Schema\Processor;
use Nette\Utils\Strings;

/**
 * Monit check manager
 */
class CheckManager extends BaseMonitManager {

	/**
	 * Returns check enablement schema
	 * @return Type Check enablement schema
	 */
	public function getEnablementSchema(): Type {
		return Expect::listOf(Expect::structure([
			'name' => Expect::string(),
			'enabled' => Expect::bool(),
		])->castTo('array'));
	}

	/**
	 * Returns list of checks and their enablement
	 * @return array<array{name: string, enabled: bool}> List of checks and their enablement
	 */
	public function list(): array {
		$availableChecks = $this->fileManager->listFiles(self::CONFIG_AVAILABLE);
		$checks = [];
		foreach ($availableChecks as $check) {
			if (!str_starts_with($check, 'check_')) {
				continue;
			}
			$checks[] = $this->get(Strings::substring($check, 6));
		}
		return $checks;
	}

	/**
	 * Returns check state
	 * @param string $name Check name
	 * @return array{name: string, enabled: bool} Check state
	 */
	public function get(string $name, bool $withContent = false): array {
		$data = [
			'name' => $name,
			'enabled' => $this->isConfigFileEnabled('check_' . $name),
		];
		if ($withContent) {
			$data['content'] = $this->fileManager->read(self::CONFIG_AVAILABLE . '/check_' . $name);
		}
		return $data;
	}

	/**
	 * Sets enablement of the checks
	 * @param array<array{name: string, enabled: bool}> $newStates List of checks and their new enablement
	 */
	public function setStates(array $newStates): void {
		$processor = new Processor();
		$newStates = $processor->process($this->getEnablementSchema(), $newStates);
		$currentStates = [];
		foreach ($this->list() as $check) {
			$currentStates[$check['name']] = $check['enabled'];
		}
		foreach ($newStates as $check) {
			if ($check['enabled'] === $currentStates[$check['name']]) {
				continue;
			}
			$this->enableConfigFile('check_' . $check['name'], $check['enabled']);
		}
	}

}
