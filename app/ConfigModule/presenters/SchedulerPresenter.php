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

use App\ConfigModule\Model\SchedulerManager;
use App\ConfigModule\Forms\ConfigSchedulerFormFactory;
use App\Presenters\BasePresenter;

class SchedulerPresenter extends BasePresenter {

	/**
	 * @var ConfigSchedulerFormFactory
	 */
	private $formFactory;

	/**
	 * @var SchedulerManager
	 */
	private $configManager;

	/**
	 * Constructor
	 * @param ConfigSchedulerFormFactory $formFactory
	 * @param SchedulerManager $configManager
	 */
	public function __construct(ConfigSchedulerFormFactory $formFactory, SchedulerManager $configManager) {
		$this->configManager = $configManager;
		$this->formFactory = $formFactory;
	}

	/**
	 * Render list tasks in scheduler
	 */
	public function renderDefault() {
		$this->onlyForAdmins();
		$this->template->tasks = $this->configManager->getTasks();
	}

	/**
	 * Edit task in scheduler
	 * @param int $id ID of MQTT interface
	 */
	public function renderEdit($id) {
		$this->onlyForAdmins();
		$this->template->id = $id;
	}

	/**
	 * Add new task to scheduler
	 * @param string $type
	 */
	public function actionAdd($type) {
		$this->onlyForAdmins();
		$this->configManager->add($type);
		$this->redirect('Scheduler:edit', ['id' => $this->configManager->getLastId()]);
		$this->setView('default');
	}

	/**
	 * Delete task in scheduler
	 * @param int $id ID of task in Scheduler
	 */
	public function actionDelete($id) {
		$this->onlyForAdmins();
		$this->configManager->delete($id);
		$this->redirect('Scheduler:default');
		$this->setView('default');
	}

	/**
	 * Create Edit task form
	 * @return Form Edit task form
	 */
	protected function createComponentConfigSchedulerForm() {
		$this->onlyForAdmins();
		return $this->formFactory->create($this);
	}

}
