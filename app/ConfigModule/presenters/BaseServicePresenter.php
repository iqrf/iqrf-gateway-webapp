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

use App\ConfigModule\Model\BaseServiceManager;
use App\ConfigModule\Forms\ConfigBaseServiceFormFactory;
use App\Presenters\BasePresenter;

class BaseServicePresenter extends BasePresenter {

	/**
	 * @var ConfigBaseServiceFormFactory Base service configuration form factory
	 */
	private $formFactory;

	/**
	 * @var BaseServiceManager Base service manager
	 */
	private $configManager;

	/**
	 * Constructor
	 * @param BaseServiceManager $configManager Base service manager
	 * @param ConfigBaseServiceFormFactory $formFactory Base service configuration form factory
	 */
	public function __construct(BaseServiceManager $configManager, ConfigBaseServiceFormFactory $formFactory) {
		$this->configManager = $configManager;
		$this->formFactory = $formFactory;
	}

	/**
	 * Render list of Base services
	 */
	public function renderDefault() {
		$this->onlyForAdmins();
		$this->template->services = $this->configManager->getServices();
	}

	/**
	 * Edit Base service
	 * @param int $id ID of Base service
	 */
	public function renderEdit(int $id) {
		$this->onlyForAdmins();
		$this->template->id = $id;
	}

	/**
	 * Delete Base service
	 * @param int $id ID of Base service
	 */
	public function actionDelete(int $id) {
		$this->onlyForAdmins();
		$this->configManager->delete($id);
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
