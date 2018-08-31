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
use App\CoreModule\Exception\NonExistingJsonSchemaException;
use App\CoreModule\Forms\FormFactory;
use Nette;
use Nette\Forms\Form;
use Nette\IOException;

/**
 * Component configuration form factory
 */
class ComponentsFormFactory {

	use Nette\SmartObject;

	/**
	 * @var ComponentManager Component configuration manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var int Component ID
	 */
	private $id;

	/**
	 * @var ComponentPresenter Component presenter
	 */
	private $presenter;

	/**
	 * Constructor
	 * @param ComponentManager $manager Generic configuration manager
	 * @param FormFactory $factory Generic form factory
	 */
	public function __construct(ComponentManager $manager, FormFactory $factory) {
		$this->manager = $manager;
		$this->factory = $factory;
	}

	/**
	 * Create components configuration form
	 * @param ComponentPresenter $presenter Component presenter
	 * @return Form Components configuration form
	 */
	public function create(ComponentPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('config.components.form'));
		$form->addText('name', 'name')->setRequired('messages.name');
		$form->addText('libraryPath', 'libraryPath');
		$form->addText('libraryName', 'libraryName')->setRequired('messages.libraryName');
		$form->addCheckbox('enabled', 'enabled');
		$form->addInteger('startlevel', 'startlevel')->setRequired('messages.startLevel');
		$form->addSubmit('save', 'Save');
		$id = $presenter->getParameter('id');
		if (isset($id)) {
			$this->id = intval($id);
			$form->setDefaults($this->manager->load($this->id));
		}
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Save component configuration
	 * @param Form $form Component configuration form
	 */
	public function save(Form $form): void {
		try {
			$array = $form->getValues(true);
			if (isset($this->id)) {
				$this->manager->save($array, $this->id);
			} else {
				$this->manager->add($array);
			}
			$this->presenter->flashMessage('config.messages.success', 'success');
		} catch (IOException $e) {
			$this->presenter->flashMessage('config.messages.writeFailure', 'danger');
		} catch (NonExistingJsonSchemaException $e) {
			$this->presenter->flashMessage('config.messages.nonExistingJsonSchema', 'danger');
		} finally {
			$this->presenter->redirect('Component:default');
		}
	}

}
