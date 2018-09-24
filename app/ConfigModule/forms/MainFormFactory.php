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

namespace App\ConfigModule\Forms;

use App\ConfigModule\Model\MainManager;
use App\ConfigModule\Presenters\MainPresenter;
use App\CoreModule\Exception\NonExistingJsonSchemaException;
use App\CoreModule\Forms\FormFactory;
use Nette;
use Nette\Forms\Form;
use Nette\IOException;

/**
 * Main configuration form factory
 */
class MainFormFactory {

	use Nette\SmartObject;

	/**
	 * @var MainManager Main configuration manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var MainPresenter Main configuration presenter
	 */
	private $presenter;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param MainManager $manager Main configuration manager
	 */
	public function __construct(FormFactory $factory, MainManager $manager) {
		$this->manager = $manager;
		$this->factory = $factory;
	}

	/**
	 * Create main configuration form
	 * @param MainPresenter $presenter Main configuration presenter
	 * @return Form Main configuration form
	 */
	public function create(MainPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('config.main.form'));
		$form->addText('applicationName', 'applicationName');
		$form->addText('resourceDir', 'resourceDir');
		$form->addText('dataDir', 'dataDir');
		$form->addText('cacheDir', 'cacheDir');
		$form->addText('userDir', 'userDir');
		$form->addText('configurationDir', 'configurationDir');
		$form->addText('deploymentDir', 'deploymentDir');
		$form->addSubmit('save', 'Save');
		$form->setDefaults($this->manager->load());
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Save main configuration
	 * @param Form $form Main configuration form
	 */
	public function save(Form $form): void {
		try {
			$this->manager->save($form->getValues(true));
			$this->presenter->flashMessage('config.messages.success', 'success');
		} catch (NonExistingJsonSchemaException $e) {
			$this->presenter->flashMessage('config.messages.writeFailures.nonExistingJsonSchema', 'danger');
		} catch (IOException $e) {
			$this->presenter->flashMessage('config.messages.writeFailures.ioError', 'danger');
		} finally {
			$this->presenter->redirect('Homepage:default');
		}
	}

}
