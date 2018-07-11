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

namespace App\ConfigModule\Presenters;

use App\ConfigModule\Forms\ConfigComponentsFormFactory;
use App\ConfigModule\Model\ComponentManager;
use App\Presenters\BasePresenter;
use Nette\Application\UI\Form;

class ComponentPresenter extends BasePresenter {

	/**
	 * @var ComponentManager Component manager
	 */
	private $configManager;

	/**
	 * @var ConfigComponentsFormFactory Daemon's components configuration form factory
	 * @inject
	 */
	public $componentsFactory;

	/**
	 * Constructor
	 * @param ComponentManager $componentManager Component manager
	 */
	public function __construct(ComponentManager $componentManager) {
		$this->configManager = $componentManager;
		parent::__construct();
	}

	/**
	 * Render Main configurator
	 */
	public function renderDefault() {
		$this->onlyForAdmins();
		$this->template->components = $this->configManager->loadComponents();
	}

	/**
	 * Edit MQTT interface
	 * @param int $id ID of MQTT interface
	 */
	public function renderEdit(int $id) {
		$this->onlyForAdmins();
		$this->template->id = $id;
	}

	/**
	 * Delete MQTT interface
	 * @param int $id ID of MQTT interface
	 */
	public function actionDelete(int $id) {
		$this->onlyForAdmins();
		$this->configManager->delete($id);
		$this->redirect('Component:default');
		$this->setView('default');
	}

	/**
	 * Create components form
	 * @return Form Components form
	 */
	protected function createComponentConfigComponentsForm(): Form {
		$this->onlyForAdmins();
		return $this->componentsFactory->create($this);
	}

}
