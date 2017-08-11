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

namespace App\ConfigModule\Presenters;

use App\ConfigModule\Forms\ConfigMqFormFactory;
use App\Model\ConfigManager;
use App\Presenters\BasePresenter;

class MqPresenter extends BasePresenter {

	/**
	 * @var ConfigMqFormFactory
	 */
	private $formFactory;

	/**
	 * @var ConfigManager
	 */
	private $configManager;

	/**
	 * Constructor
	 * @param ConfigMqFormFactory $formFactory
	 * @param ConfigManager $configManager
	 */
	public function __construct(ConfigMqFormFactory $formFactory, ConfigManager $configManager) {
		$this->configManager = $configManager;
		$this->formFactory = $formFactory;
	}

	/**
	 *
	 */
	public function renderDefault() {
		$this->onlyForAdmins();
	}

	/**
	 * Create MQ interface form
	 * @return Form MQ interface form
	 */
	protected function createComponentConfigMqForm() {
		$this->onlyForAdmins();
		return $this->formFactory->create($this);
	}

}
