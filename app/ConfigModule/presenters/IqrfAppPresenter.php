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

use App\ConfigModule\Forms\ConfigIqrfAppFormFactory;
use App\Presenters\BasePresenter;
use App\Model\ConfigManager;

class IqrfAppPresenter extends BasePresenter {

	/**
	 * @var ConfigIqrfAppFormFactory
	 */
	private $formFactory;

	/**
	 * @var ConfigManager
	 */
	private $configManager;

	/**
	 * Constructor
	 * @param ConfigIqrfAppFormFactory $formFactory
	 * @param ConfigManager $configManager
	 */
	public function __construct(ConfigIqrfAppFormFactory $formFactory, ConfigManager $configManager) {
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
	protected function createComponentConfigIqrfAppForm() {
		$this->onlyForAdmins();
		return $this->formFactory->create($this);
	}

}
