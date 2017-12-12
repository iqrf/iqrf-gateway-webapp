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

declare(strict_types=1);

namespace App\ConfigModule\Presenters;

use App\ConfigModule\Forms\ConfigIqrfFormFactory;
use App\ConfigModule\Model\IqrfManager;
use App\Presenters\BasePresenter;

class IqrfPresenter extends BasePresenter {

	/**
	 * @var ConfigIqrfFormFactory
	 */
	private $formFactory;

	/**
	 * @var IqrfManager
	 */
	private $iqrfManager;

	/**
	 * Constructor
	 * @param ConfigIqrfFormFactory $formFactory
	 * @param IqrfManager $iqrfManager
	 */
	public function __construct(ConfigIqrfFormFactory $formFactory, IqrfManager $iqrfManager) {
		$this->formFactory = $formFactory;
		$this->iqrfManager = $iqrfManager;
	}

	/**
	 * Render IQRF interface configurator
	 */
	public function renderDefault() {
		$this->onlyForAdmins();
		$this->template->interfaces = $this->iqrfManager->getInterfaces();
	}

	/**
	 * Create IQRF interface form
	 * @return Form IQRF interface form
	 */
	protected function createComponentConfigIqrfForm() {
		$this->onlyForAdmins();
		return $this->formFactory->create($this);
	}

}
