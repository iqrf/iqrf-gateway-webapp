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

use App\ConfigModule\Exceptions\IncompleteConfigurationException;
use App\ConfigModule\Exceptions\InvalidConfigurationFormatException;
use App\ConfigModule\Models\MigrationManager;
use App\ConfigModule\Presenters\MigrationPresenter;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use App\CoreModule\Forms\FormFactory;
use App\ServiceModule\Exceptions\UnsupportedInitSystemException;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\IOException;
use Nette\Utils\JsonException;

/**
 * Configuration migration form factory
 */
class MigrationFormFactory {

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
	 * Creates the configuration migration form
	 * @param MigrationPresenter $presenter Configuration migration presenter
	 * @return Form Configuration migration form
	 */
	public function create(MigrationPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create('config.migration');
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
	 * Imports the configuration
	 * @param SubmitButton $button Configuration import button
	 * @throws JsonException
	 */
	public function import(SubmitButton $button): void {
		try {
			$values = $button->getForm()->getValues('array');
			assert(is_array($values));
			$this->manager->upload($values);
			$this->presenter->flashSuccess('config.migration.messages.importedConfig');
		} catch (IncompleteConfigurationException $e) {
			$this->presenter->flashError('config.migration.errors.invalidConfig');
		} catch (InvalidConfigurationFormatException $e) {
			$this->presenter->flashError('config.migration.errors.invalidFormat');
		} catch (NonexistentJsonSchemaException $e) {
			$this->presenter->flashError('config.messages.writeFailures.nonExistingJsonSchema');
		} catch (UnsupportedInitSystemException $e) {
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
		$this->presenter->redirect('Migration:export');
	}

}
