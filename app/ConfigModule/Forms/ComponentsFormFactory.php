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

use App\ConfigModule\Models\ComponentManager;
use App\ConfigModule\Presenters\ComponentPresenter;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use App\CoreModule\Forms\FormFactory;
use Nette\Application\UI\Form;
use Nette\IOException;
use Nette\Utils\JsonException;

/**
 * Component configuration form factory
 */
class ComponentsFormFactory {

	/**
	 * @var ComponentManager Component configuration manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

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
	 * Creates the components configuration form
	 * @param ComponentPresenter $presenter Component presenter
	 * @return Form Components configuration form
	 */
	public function create(ComponentPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create('config.components.form');
		$form->addText('name', 'name')
			->setRequired('messages.name');
		$form->addText('libraryPath', 'libraryPath');
		$form->addText('libraryName', 'libraryName')
			->setRequired('messages.libraryName');
		$form->addCheckbox('enabled', 'enabled');
		$form->addInteger('startlevel', 'startlevel')
			->setRequired('messages.startLevel');
		if ($presenter->getParameter('id') === null) {
			$form->addSubmit('add', 'add');
		} else {
			$form->addSubmit('save', 'save');
		}
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Saves the component configuration
	 * @param Form $form Component configuration form
	 */
	public function save(Form $form): void {
		try {
			$values = $form->getValues('array');
			assert(is_array($values));
			$id = $this->presenter->getParameter('id');
			if (isset($id)) {
				$this->manager->save($values, (int) $id);
			} else {
				$this->manager->add($values);
			}
			$this->presenter->flashSuccess('config.messages.success');
		} catch (IOException $e) {
			$this->presenter->flashError('config.messages.writeFailures.ioError');
		} catch (NonexistentJsonSchemaException $e) {
			$this->presenter->flashError('config.messages.writeFailures.nonExistingJsonSchema');
		} catch (JsonException $e) {
			$this->presenter->flashError('config.messages.writeFailures.invalidJson');
		} finally {
			$this->presenter->redirect('Component:default');
		}
	}

}
