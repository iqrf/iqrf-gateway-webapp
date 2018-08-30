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
declare(strict_types=1);

namespace App\ConfigModule\Presenters;

use App\ConfigModule\Model\SchedulerManager;
use App\ConfigModule\Forms\SchedulerFormFactory;
use App\CoreModule\Presenters\ProtectedPresenter;
use Nette\Forms\Form;
use Nette\IOException;
use Nette\Utils\JsonException;

/**
 * Scheduler configuration presenter
 */
class SchedulerPresenter extends ProtectedPresenter {

	/**
	 * @var SchedulerFormFactory Scheduler's task configuration form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * @var SchedulerManager Scheduler's task manager
	 */
	private $configManager;

	/**
	 * Constructor
	 * @param SchedulerManager $configManager Scheduler's task manager
	 */
	public function __construct(SchedulerManager $configManager) {
		$this->configManager = $configManager;
		parent::__construct();
	}

	/**
	 * Catch exceptions
	 */
	public function actionDefault(): void {
		try {
			$this->configManager->getTasks();
		} catch (IOException $e) {
			$this->flashMessage('config.messages.readFailure', 'danger');
			$this->redirect('Homepage:default');
		} catch (JsonException $e) {
			$this->flashMessage('config.messages.invalidJson', 'danger');
			$this->redirect('Homepage:default');
		}
	}

	/**
	 * Render list tasks in scheduler
	 */
	public function renderDefault(): void {
		$this->template->tasks = $this->configManager->getTasks();
	}

	/**
	 * Edit task in scheduler
	 * @param int $id ID of task in Scheduler
	 */
	public function renderEdit(int $id): void {
		$this->template->id = $id;
	}

	/**
	 * Add new task to scheduler
	 * @param string $type
	 */
	public function actionAdd(string $type): void {
		$this->configManager->add($type);
		$this->redirect('Scheduler:edit', ['id' => $this->configManager->getLastId()]);
		$this->setView('default');
	}

	/**
	 * Delete task in scheduler
	 * @param int $id ID of task in Scheduler
	 */
	public function actionDelete(int $id): void {
		$this->configManager->delete($id);
		$this->redirect('Scheduler:default');
		$this->setView('default');
	}

	/**
	 * Create Edit task form
	 * @return Form Edit task form
	 */
	protected function createComponentConfigSchedulerForm(): Form {
		return $this->formFactory->create($this);
	}

}
