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

use App\ConfigModule\Forms\MigrationFormFactory;
use App\ConfigModule\Forms\SchedulerMigrationFormFactory;
use App\ConfigModule\Models\MigrationManager;
use App\ConfigModule\Models\SchedulerMigrationManager;
use App\CoreModule\Presenters\ProtectedPresenter;
use Nette\Application\BadRequestException;
use Nette\Forms\Form;
use Tracy\Debugger;

/**
 * Configuration migration presenter
 */
class MigrationPresenter extends ProtectedPresenter {

	/**
	 * @var MigrationFormFactory Configuration import form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * @var SchedulerMigrationFormFactory Scheduler's configuration import form factory
	 * @inject
	 */
	public $schedulerFormFactory;

	/**
	 * @var MigrationManager Configuration migration manager
	 */
	private $manager;

	/**
	 * @var SchedulerMigrationManager Scheduler's configuration migration manager
	 */
	private $schedulerManager;

	/**
	 * Constructor
	 * @param MigrationManager $manager Configuration migration manager
	 */
	public function __construct(MigrationManager $manager, SchedulerMigrationManager $schedulerManager) {
		$this->manager = $manager;
		$this->schedulerManager = $schedulerManager;
		parent::__construct();
	}

	/**
	 * Export a configuration action
	 */
	public function actionExport(): void {
		try {
			$this->sendResponse($this->manager->download());
		} catch (BadRequestException $e) {
			Debugger::log('Cannot read zip archive with a configuration.');
			$this->flashMessage('config.migration.errors.readConfig', 'danger');
			$this->redirect('Migration:default');
			$this->setView('default');
		}
	}

	/**
	 * Export a scheduler's configuration action
	 */
	public function actionSchedulerExport(): void {
		try {
			$this->sendResponse($this->schedulerManager->download());
		} catch (BadRequestException $e) {
			Debugger::log('Cannot read JSON file with a scheduler\'s configuration.');
			$this->flashMessage('config.schedulerMigration.errors.readConfig', 'danger');
			$this->redirect('Migration:default');
			$this->setView('default');
		}
	}

	/**
	 * Create configuration import form
	 * @return Form Configuration import form
	 */
	protected function createComponentConfigImportForm(): Form {
		return $this->formFactory->create($this);
	}

	/**
	 * Create scheduler's configuration import form
	 * @return Form Scheduler's configuration import form
	 */
	protected function createComponentConfigSchedulerImportForm(): Form {
		return $this->schedulerFormFactory->create($this);
	}

}
