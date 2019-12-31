<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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
use App\ConfigModule\Forms\SchedulerImportFormFactory;
use App\ConfigModule\Models\SchedulerManager;
use App\CoreModule\Presenters\ProtectedPresenter;
use App\CoreModule\Traits\TPresenterFlashMessage;
use Nette\Application\UI\Form;
use Nette\IOException;
use Nette\Utils\JsonException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * Scheduler configuration presenter
 */
class SchedulerPresenter extends ProtectedPresenter {

	use TPresenterFlashMessage;

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
	 * @var SchedulerImportFormFactory Scheduler's task import form factory
	 * @inject
	 */
	public $importFormFactory;

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
	 * Renders a list of tasks in scheduler
	 */
	public function renderDefault(): void {
		try {
			$this->template->tasks = $this->configManager->list();
		} catch (IOException $e) {
			$this->flashError('config.messages.readFailures.ioError');
			$this->redirect('Homepage:default');
		} catch (JsonException $e) {
			$this->flashError('config.messages.readFailures.invalidJson');
			$this->redirect('Homepage:default');
		}
	}

	/**
	 * Adds the task into scheduler
	 * @param string $type Task's message type
	 */
	public function renderAdd(string $type): void {
		$this->template->type = $type;
	}

	/**
	 * Edits the task in scheduler
	 * @param int $id ID of task in Scheduler
	 */
	public function renderEdit(int $id): void {
		$this->template->id = $id;
	}

	/**
	 * Deletes the task in scheduler
	 * @param int $id ID of task in Scheduler
	 */
	public function actionDelete(int $id): void {
		try {
			$this->configManager->delete($id);
			$this->flashSuccess('config.messages.successes.delete');
		} catch (IOException $e) {
			$this->flashError('config.messages.writeFailures.ioError');
		} catch (JsonException $e) {
			$this->flashError('config.messages.writeFailures.invalidJson');
		}
		$this->redirect('Scheduler:default');
	}

	/**
	 * Creates the scheduler's tasks data grid
	 * @param string $name Data grid's component name
	 * @return DataGrid Scheduler's tasks data grid
	 * @throws DataGridException
	 * @throws JsonException
	 */
	protected function createComponentConfigSchedulerDataGrid(string $name): DataGrid {
		return $this->dataGridFactory->create($this, $name);
	}

	/**
	 * Creates the Edit task form
	 * @return Form Edit task form
	 * @throws JsonException
	 */
	protected function createComponentConfigSchedulerForm(): Form {
		return $this->formFactory->create($this);
	}

	/**
	 * Creates the tash import form
	 * @return Form Task import form
	 * @throws JsonException
	 */
	protected function createComponentConfigSchedulerImportForm(): Form {
		return $this->importFormFactory->create($this);
	}

}
