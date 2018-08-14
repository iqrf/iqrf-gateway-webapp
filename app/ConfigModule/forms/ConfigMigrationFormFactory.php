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

namespace App\ConfigModule\Forms;

use App\ConfigModule\Model\IncompleteConfiguration;
use App\ConfigModule\Model\InvalidConfigurationFormat;
use App\ConfigModule\Model\MigrationManager;
use App\ConfigModule\Presenters\MigrationPresenter;
use App\Forms\FormFactory;
use App\Model\NonExistingJsonSchemaException;
use Nette;
use Nette\Forms\Form;
use Nette\IOException;

/**
 * Configuration migration form factory
 */
class ConfigMigrationFormFactory {

	use Nette\SmartObject;

	/**
	 * @var MigrationManager Configuration migration manager
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
	 * @param MigrationManager $manager Configuration migration manager
	 */
	public function __construct(FormFactory $factory, MigrationManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Create configuration migration form
	 * @param MigrationPresenter $presenter Configuration migration presenter
	 * @return Form Configuration migration form
	 */
	public function create(MigrationPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('config.migration'));
		$form->addUpload('configuration', 'configuration')->setRequired('messages.configuration');
		$form->addSubmit('import', 'import');
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = [$this, 'import'];
		return $form;
	}

	/**
	 * Import a configuration
	 * @param Form $form Configuration migration form
	 */
	public function import(Form $form) {
		try {
			$this->manager->upload($form->getValues());
			$this->presenter->flashMessage('config.migration.messages.importedConfig', 'success');
		} catch (\Exception $e) {
			if ($e instanceof IncompleteConfiguration) {
				$this->presenter->flashMessage('config.migration.errors.invalidConfig', 'danger');
			} else if ($e instanceof InvalidConfigurationFormat) {
				$this->presenter->flashMessage('config.migration.errors.invalidFormat', 'danger');
			} else if ($e instanceof NonExistingJsonSchemaException) {
				$this->presenter->flashMessage('config.messages.nonExistingJsonSchema', 'danger');
			} else if ($e instanceof IOException) {
				/**
				 * @todo Custom error message.
				 */
				$$this->presenter->flashMessage('config.messages.writeFailure', 'danger');
			} else {
				throw $e;
			}
		} finally {
			$this->presenter->redirect('Homepage:default');
		}
	}

}
