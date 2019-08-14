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

namespace App\ConfigModule\Forms;

use App\ConfigModule\Exceptions\InvalidConfigurationFormatException;
use App\ConfigModule\Models\SchedulerMigrationManager;
use App\ConfigModule\Presenters\MigrationPresenter;
use App\CoreModule\Exceptions\NonExistingJsonSchemaException;
use App\CoreModule\Forms\FormFactory;
use App\ServiceModule\Exceptions\NotSupportedInitSystemException;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\IOException;
use Nette\SmartObject;

/**
 * Scheduler's configuration migration form factory
 */
class SchedulerMigrationFormFactory {

	use SmartObject;

	/**
	 * @var SchedulerMigrationManager Scheduler's configuration migration manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var MigrationPresenter Configuration migration presenter
	 */
	private $presenter;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param SchedulerMigrationManager $manager Scheduler's configuration migration manager
	 */
	public function __construct(FormFactory $factory, SchedulerMigrationManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Creates the configuration migration form
	 * @param MigrationPresenter $presenter Configuration migration presenter
	 * @return Form Configuration migration form
	 */
	public function create(MigrationPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create('config.schedulerMigration');
		$form->addUpload('configuration', 'configuration')
			->setRequired('messages.configuration')
			->setHtmlAttribute('accept', '.zip');
		$form->addSubmit('import', 'import')
			->onClick[] = [$this, 'import'];
		$form->addSubmit('export', 'export')
			->setValidationScope([])
			->onClick[] = [$this, 'export'];
		$form->addProtection('core.errors.form-timeout');
		return $form;
	}

	/**
	 * Imports the scheduler's configuration
	 * @param SubmitButton $button Scheduler's configuration import button
	 */
	public function import(SubmitButton $button): void {
		try {
			$this->manager->upload($button->getForm()->getValues(true));
			$this->presenter->flashSuccess('config.migration.messages.importedConfig');
		} catch (InvalidConfigurationFormatException $e) {
			$this->presenter->flashError('config.migration.errors.invalidFormat');
		} catch (NonExistingJsonSchemaException $e) {
			$this->presenter->flashError('config.messages.writeFailures.nonExistingJsonSchema');
		} catch (NotSupportedInitSystemException $e) {
			$this->presenter->flashError('service.errors.unsupportedInit');
		} catch (IOException $e) {
			/// TODO: Use custom error message.
			$this->presenter->flashError('config.messages.writeFailures.ioError');
		} finally {
			$this->presenter->redirect('Homepage:default');
		}
	}

	/**
	 * Exports the configuration
	 */
	public function export(): void {
		$this->presenter->redirect('Migration:schedulerExport');
	}

}
