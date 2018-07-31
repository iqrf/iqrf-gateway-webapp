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

use App\ConfigModule\Model\GenericManager;
use App\ConfigModule\Presenters\JsonRawApiPresenter;
use App\Forms\FormFactory;
use App\Model\NonExistingJsonSchema;
use Nette;
use Nette\Forms\Form;
use Nette\IOException;

class ConfigJsonDpaApiRawFormFactory {

	use Nette\SmartObject;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var JsonRawApiPresenter JSON Raw API configuration presenter
	 */
	private $presenter;

	/**
	 * Constructor
	 * @param GenericManager $manager Generic configuration manager
	 * @param FormFactory $factory Generic form factory
	 */
	public function __construct(GenericManager $manager, FormFactory $factory) {
		$this->manager = $manager;
		$this->factory = $factory;
		$this->manager->setComponent('iqrf::JsonDpaApiRaw');
	}

	/**
	 * Create JSON splitter service configuration form
	 * @param JsonRawApiPresenter $presenter JSON Raw API configuration presenter
	 * @return Form JSON splitter configuration form
	 */
	public function create(JsonRawApiPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('config.jsonRawApi.form'));
		$this->manager->setFileName($this->manager->getInstanceFiles()[0]);
		$form->addText('instance', 'instance')->setRequired('messages.instance');
		$form->addCheckbox('asyncDpaMessage', 'asyncDpaMessage');
		$form->addSubmit('save', 'Save');
		$form->addProtection('core.errors.form-timeout');
		$form->setDefaults($this->manager->load());
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Save JSON Raw API configuration
	 * @param Form $form JSON Raw API configuration form
	 */
	public function save(Form $form) {
		try {
			$this->manager->save($form->getValues());
			$this->presenter->flashMessage('config.messages.success', 'success');
		} catch (\Exception $e) {
			if ($e instanceof NonExistingJsonSchema) {
				$this->presenter->flashMessage('config.messages.nonExistingJsonSchema', 'danger');
			} else if ($e instanceof IOException) {
				$this->presenter->flashMessage('config.messages.writeFailure', 'danger');
			}
		} finally {
			$this->presenter->redirect('Homepage:default');
		}
	}

}
