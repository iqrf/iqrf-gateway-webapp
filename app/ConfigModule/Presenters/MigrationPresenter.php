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
use App\CoreModule\Traits\TPresenterFlashMessage;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;

/**
 * Configuration migration presenter
 */
class MigrationPresenter extends ProtectedPresenter {

	use TPresenterFlashMessage;

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
	 * @param SchedulerMigrationManager $schedulerManager Scheduler's configuration migration manager
	 */
	public function __construct(MigrationManager $manager, SchedulerMigrationManager $schedulerManager) {
		$this->manager = $manager;
		$this->schedulerManager = $schedulerManager;
		parent::__construct();
	}

	/**
	 * Exports a configuration action
	 */
	public function actionExport(): void {
		try {
			$this->sendResponse($this->manager->download());
		} catch (BadRequestException $e) {
			$this->flashError('config.migration.errors.readConfig');
			$this->redirect('Migration:default');
		}
	}

	/**
	 * Exports a scheduler's configuration action
	 */
	public function actionSchedulerExport(): void {
		try {
			$this->sendResponse($this->schedulerManager->download());
		} catch (BadRequestException $e) {
			$this->flashError('config.schedulerMigration.errors.readConfig');
			$this->redirect('Migration:default');
		}
	}

	/**
	 * Creates the configuration import form
	 * @return Form Configuration import form
	 */
	protected function createComponentConfigImportForm(): Form {
		return $this->formFactory->create($this);
	}

	/**
	 * Creates the scheduler's configuration import form
	 * @return Form Scheduler's configuration import form
	 */
	protected function createComponentConfigSchedulerImportForm(): Form {
		return $this->schedulerFormFactory->create($this);
	}

}
