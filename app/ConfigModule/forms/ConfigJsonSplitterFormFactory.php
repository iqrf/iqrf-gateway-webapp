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

use App\ConfigModule\Forms\GenericConfigFormFactory;
use App\ConfigModule\Model\GenericManager;
use App\ConfigModule\Presenters\JsonSplitterPresenter;
use App\Forms\FormFactory;
use Nette;
use Nette\Forms\Form;

/**
 * JSON Splitter form factory
 */
class ConfigJsonSplitterFormFactory extends GenericConfigFormFactory {

	use Nette\SmartObject;

	/**
	 * Constructor
	 * @param GenericManager $manager Generic configuration manager
	 * @param FormFactory $factory Generic form factory
	 */
	public function __construct(GenericManager $manager, FormFactory $factory) {
		parent::__construct($manager, $factory);
		$this->manager->setComponent('iqrf::JsonSplitter');
	}

	/**
	 * Create JSON splitter service configuration form
	 * @param JsonSplitterPresenter $presenter JSON Splitter settings presenter
	 * @return Form JSON splitter configuration form
	 */
	public function create(JsonSplitterPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('config.jsonSplitter.form'));
		$this->manager->setFileName($this->manager->getInstanceFiles()[0]);
		$form->addText('instance', 'instance')->setRequired('messages.instance');
		$form->addCheckbox('validateJsonResponse', 'validateJsonResponse');
		$form->addSubmit('save', 'Save');
		$form->addProtection('core.errors.form-timeout');
		$form->setDefaults($this->manager->load());
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

}
