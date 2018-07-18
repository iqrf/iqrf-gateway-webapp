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

use App\ConfigModule\Model\ComponentManager;
use App\ConfigModule\Presenters\ComponentPresenter;
use App\Forms\FormFactory;
use Nette;
use Nette\Forms\Form;

class ConfigComponentsFormFactory {

	use Nette\SmartObject;

	/**
	 * @var ComponentManager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * Constructor
	 * @param ComponentManager $manager
	 * @param FormFactory $factory Generic form factory
	 */
	public function __construct(ComponentManager $manager, FormFactory $factory) {
		$this->manager = $manager;
		$this->factory = $factory;
	}

	/**
	 * Create components configuration form
	 * @param ComponentPresenter $presenter
	 * @return Form Components configuration form
	 */
	public function create(ComponentPresenter $presenter): Form {
		$id = intval($presenter->getParameter('id'));
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('config.components.form'));
		$form->addText('name', 'name')->setRequired('messages.name');
		$form->addText('libraryPath', 'libraryPath');
		$form->addText('libraryName', 'libraryName')->setRequired('messages.libraryName');
		$form->addCheckbox('enabled', 'enabled');
		$form->addInteger('startlevel', 'startlevel')->setRequired('messages.startLevel');
		$form->addSubmit('save', 'Save');
		$form->setDefaults($this->manager->loadComponent($id));
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter, $id) {
			$this->manager->save($values, $id);
			$presenter->flashMessage('config.messages.success', 'success');
			$presenter->redirect('Component:default');
		};
		return $form;
	}

}
