<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
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

namespace App\Presenters;

use App\Model\ConfigManager;
use App\Forms;

/**
 * Configuration presenter
 */
class ConfigPresenter extends BasePresenter {

	/**
	 * @var Forms\ConfigComponentsFormFactory
	 * @inject
	 */
	public $configComponentsFactory;

	/**
	 * @var Forms\ConfigUdpFormFactory
	 * @inject
	 */
	public $configUdpFactory;


	/**
	 * @var ConfigManager
	 * @inject
	 */
	private $configManager;

	/**
	 * @param ConfigManager $configManager
	 */
	public function __construct(ConfigManager $configManager) {
		$this->configManager = $configManager;
	}

	public function renderDefault() {

	}

	/**
	 * Create components form
	 * @return Form Components form
	 */
	protected function createComponentConfigComponentsForm() {
		$formFactory = $this->configComponentsFactory;
		return $formFactory->create($this);
	}

	/**
	 * Create UDP form
	 * @return Form UDP form
	 */
	protected function createComponentConfigUdpForm() {
		$formFactory = $this->configUdpFactory;
		return $formFactory->create($this);
	}

}
