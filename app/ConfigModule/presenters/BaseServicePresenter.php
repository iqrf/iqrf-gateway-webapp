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

use App\Forms\ConfigBaseServiceFormFactory;
use App\Presenters\BasePresenter;
use App\Model\ConfigManager;

class BaseServicePresenter extends BasePresenter {

	/**
	 * @var ConfigBaseServiceFormFactory
	 */
	private $formFactory;
	
	/**
	 * @var ConfigManager
	 */
	private $configManager;
	
	/**
	 * Constructor
	 * @param ConfigBaseServiceFormFactory $formFactory
	 * @param ConfigManager $configManager
	 */
	public function __construct(ConfigBaseServiceFormFactory $formFactory, ConfigManager $configManager) {
		$this->configManager = $configManager;
		$this->formFactory = $formFactory;
	}
	
	/**
	 * Render list of Base services
	 */
	public function renderDefault() {
		$this->onlyForAdmins();
		$this->template->services = $this->configManager->read('BaseService')['Instances'];
	}
	
	/**
	 * Edit Base service
	 * @param int $id ID of Base service
	 */
	public function renderEdit($id) {
		$this->onlyForAdmins();
		$this->template->id = $id;
	}

	/**
	 * Delete Base service
	 * @param int $id ID of Base service
	 */
	public function actionDelete($id) {
		$this->onlyForAdmins();
		$this->configManager->deleteBaseService($id);
		$this->redirect('BaseService:default');
		$this->setView('baseService');
	}

	/**
	 * Create Base service form
	 * @return Form Base service form
	 */
	protected function createComponentConfigBaseServiceForm() {
		$this->onlyForAdmins();
		return $this->formFactory->create($this);
	}

}
