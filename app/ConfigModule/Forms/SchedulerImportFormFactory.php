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
use App\ConfigModule\Models\SchedulerManager;
use App\ConfigModule\Models\SchedulerMigrationManager;
use App\ConfigModule\Presenters\SchedulerPresenter;
use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\NonExistingJsonSchemaException;
use App\CoreModule\Forms\FormFactory;
use App\ServiceModule\Exceptions\NotSupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use Nette\IOException;
use Nette\SmartObject;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Scheduler's task import form factory
 */
class SchedulerImportFormFactory {

	use SmartObject;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var SchedulerPresenter Scheduler presenter
	 */
	private $presenter;

	/**
	 * @var SchedulerManager Scheduler manager
	 */
	private $manager;

	/**
	 * @var SchedulerMigrationManager Scheduler migration manager
	 */
	private $migrationManager;

	/**
	 * @var ServiceManager IQRF Gateway Daemon service manager
	 */
	private $serviceManager;

	/**
	 * Translator prefix
	 */
	private const TRANSLATOR_PREFIX = 'config.scheduler.importForm';

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param SchedulerManager $manager Scheduler manager
	 * @param SchedulerMigrationManager $migrationManager Scheduler migration manager
	 * @param ServiceManager $serviceManager IQRF Gateway Daemon service manager
	 */
	public function __construct(FormFactory $factory, SchedulerManager $manager, SchedulerMigrationManager $migrationManager, ServiceManager $serviceManager) {
		$this->factory = $factory;
		$this->manager = $manager;
		$this->migrationManager = $migrationManager;
		$this->serviceManager = $serviceManager;
	}

	/**
	 * Creates the task import form
	 * @param SchedulerPresenter $presenter Scheduler presenter
	 * @return Form Task import form
	 */
	public function create(SchedulerPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create(self::TRANSLATOR_PREFIX);
		$form->addUpload('file', 'file')
			->setRequired('messages.file')
			->setHtmlAttribute('accept', 'application/json,application/zip');
		$form->addSubmit('import', 'import');
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = [$this, 'import'];
		return $form;
	}

	/**
	 * Imports a file
	 * @param Form $form Scheduler configuration import form
	 */
	public function import(Form $form): void {
		/**
		 * @var FileUpload $file JSON file
		 */
		$file = $form->getValues()->file;
		if ($file->getContentType() === 'text/plain' ||
			$file->getContentType() === 'application/json') {
			$this->importJson($form, $file);
		} elseif ($file->getContentType() === 'application/zip') {
			$this->importZip($form, $file);
		} else {
			$this->presenter->flashError(self::TRANSLATOR_PREFIX . '.messages.invalidFile');
		}
	}

	/**
	 * Imports JSON file with task configuration
	 * @param Form $form Scheduler configuration import form
	 * @param FileUpload $file JSON file with task configuration
	 */
	private function importJson(Form $form, FileUpload $file): void {
		try {
			$json = Json::decode($file->getContents());
		} catch (JsonException $e) {
			$form['file']->addError('messages.invalidFile');
			$this->presenter->flashError(self::TRANSLATOR_PREFIX . '.messages.invalidFile');
			return;
		}
		try {
			$this->manager->save($json);
			$this->serviceManager->restart();
			$this->presenter->flashSuccess(self::TRANSLATOR_PREFIX . '.messages.success');
			$this->presenter->flashInfo('service.actions.restart.message');
			$this->presenter->redirect('Scheduler:default');
		} catch (IOException $e) {
			$this->presenter->flashError('config.messages.writeFailures.ioError');
		} catch (JsonException $e) {
			$this->presenter->flashError('config.messages.writeFailures.invalidJson');
		} catch (NonExistingJsonSchemaException $e) {
			$this->presenter->flashError($e->getMessage());
		} catch (InvalidJsonException $e) {
			$this->presenter->flashError($e->getMessage());
		}
	}

	/**
	 * Imports ZIP archive with scheduler configuration
	 * @param Form $form Scheduler configuration import form
	 * @param FileUpload $file ZIP archive with scheduler configuration
	 */
	private function importZip(Form $form, FileUpload $file): void {
		try {
			$this->migrationManager->upload($file);
			$this->serviceManager->restart();
			$this->presenter->flashSuccess('config.migration.messages.importedConfig');
			$this->presenter->flashInfo('service.actions.restart.message');
			$this->presenter->redirect('Scheduler:default');
		} catch (InvalidConfigurationFormatException $e) {
			$this->presenter->flashError('config.migration.errors.invalidFormat');
		} catch (NonExistingJsonSchemaException $e) {
			$this->presenter->flashError($e->getMessage());
		} catch (InvalidJsonException $e) {
			$this->presenter->flashError($e->getMessage());
		} catch (NotSupportedInitSystemException $e) {
			$this->presenter->flashError('service.errors.unsupportedInit');
		} catch (IOException $e) {
			/// TODO: Use custom error message.
			$this->presenter->flashError('config.messages.writeFailures.ioError');
		} catch (JsonException $e) {
			$this->presenter->flashError('config.messages.writeFailures.invalidJson');
		}
	}

}
