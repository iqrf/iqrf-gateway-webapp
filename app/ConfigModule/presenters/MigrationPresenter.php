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

use App\ConfigModule\Forms\MigrationFormFactory;
use App\ConfigModule\Model\MigrationManager;
use App\CoreModule\Presenters\ProtectedPresenter;
use Nette\Application\BadRequestException;
use Nette\Forms\Form;
use Tracy\Debugger;

/**
 * Confoguration migration presenter
 */
class MigrationPresenter extends ProtectedPresenter {

	/**
	 * @var MigrationFormFactory Configuration import form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * @var MigrationManager Configuration migration manager
	 */
	private $migrationManager;

	/**
	 * Constructor
	 * @param MigrationManager $migrationManager Configuration migration manager
	 */
	public function __construct(MigrationManager $migrationManager) {
		$this->migrationManager = $migrationManager;
		parent::__construct();
	}

	/**
	 * Export a configuration action
	 */
	public function actionExport(): void {
		try {
			$this->sendResponse($this->migrationManager->download());
		} catch (BadRequestException $e) {
			Debugger::log('Cannot read zip archive with a configuration.');
			$this->flashMessage('config.migration.errors.readConfig', 'danger');
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

}
