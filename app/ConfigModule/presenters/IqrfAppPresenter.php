<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017 IQRF Tech s.r.o.
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

class IqrfAppPresenter extends BasePresenter {

	/**
	 * @var ConfigIqrfAppFormFactory
	 */
	private $formFactory;

	/**
	 * Constructor
	 * @param ConfigIqrfAppFormFactory $formFactory
	 */
	public function __construct(ConfigIqrfAppFormFactory $formFactory) {
		$this->formFactory = $formFactory;
	}

	/**
	 * Render IqrfApp configurator
	 */
	public function renderDefault() {
		$this->onlyForAdmins();
	}

	/**
	 * Create IqrfApp form
	 * @return Form IqrfApp form
	 */
	protected function createComponentConfigIqrfAppForm() {
		$this->onlyForAdmins();
		return $this->formFactory->create($this);
	}

}
