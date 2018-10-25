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

use App\ConfigModule\Datagrids\SchedulerDataGridFactory;
use App\ConfigModule\Forms\SchedulerFormFactory;
use App\ConfigModule\Models\SchedulerManager;
use App\CoreModule\Presenters\ProtectedPresenter;
use Nette\Forms\Form;
use Nette\IOException;
use Nette\Utils\JsonException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * Scheduler configuration presenter
 */
class SchedulerPresenter extends ProtectedPresenter {

	/**
	 * @var SchedulerDataGridFactory Scheduler's tasks data grid
	 * @inject
	 */
	public $dataGridFactory;

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
	 * Render list tasks in scheduler
	 */
	public function renderDefault(): void {
		try {
			$this->template->tasks = $this->configManager->list();
		} catch (IOException $e) {
			$this->flashMessage('config.messages.readFailures.ioErrors', 'danger');
			$this->redirect('Homepage:default');
		} catch (JsonException $e) {
			$this->flashMessage('config.messages.readFailures.invalidJson', 'danger');
			$this->redirect('Homepage:default');
		}
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
		try {
			$this->configManager->add($type);
			$this->redirect('Scheduler:edit', ['id' => $this->configManager->getLastId()]);
			$this->setView('default');
		} catch (IOException $e) {
			$this->flashMessage('config.messages.writeFailures.ioError', 'danger');
			$this->redirect('Homepage:default');
		} catch (JsonException $e) {
			$this->flashMessage('config.messages.writeFailures.invalidJson', 'danger');
			$this->redirect('Homepage:default');
		}
	}

	/**
	 * Delete task in scheduler
	 * @param int $id ID of task in Scheduler
	 */
	public function actionDelete(int $id): void {
		try {
			$this->configManager->delete($id);
			$this->redirect('Scheduler:default');
			$this->setView('default');
		} catch (IOException $e) {
			$this->flashMessage('config.messages.writeFailures.ioError', 'danger');
			$this->redirect('Homepage:default');
		} catch (JsonException $e) {
			$this->flashMessage('config.messages.writeFailures.invalidJson', 'danger');
			$this->redirect('Homepage:default');
		}
	}

	/**
	 * Create scheduler's tasks data grid
	 * @param string $name Data grid's component name
	 * @return DataGrid Scheduler's tasks data grid
	 * @throws DataGridException
	 * @throws JsonException
	 */
	protected function createComponentConfigSchedulerDataGrid(string $name): DataGrid {
		return $this->dataGridFactory->create($this, $name);
	}

	/**
	 * Create Edit task form
	 * @return Form Edit task form
	 * @throws JsonException
	 */
	protected function createComponentConfigSchedulerForm(): Form {
		return $this->formFactory->create($this);
	}

}
