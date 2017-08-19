<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
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

namespace App\ConfigModule\Forms;

use App\ConfigModule\Model\SchedulerManager;
use App\ConfigModule\Presenters\SchedulerPresenter;
use App\Forms\FormFactory;
use Nette;
use Nette\Application\UI\Form;

class ConfigSchedulerFormFactory {

	use Nette\SmartObject;

	/**
	 * @var SchedulerManager
	 */
	private $manager;

	/**
	 * @var FormFactory
	 */
	private $factory;

	/**
	 * Constructor
	 * @param FormFactory $factory
	 * @param SchedulerManager $manager
	 */
	public function __construct(FormFactory $factory, SchedulerManager $manager) {
		$this->manager = $manager;
		$this->factory = $factory;
	}

	/**
	 * Create Scheduler configuration form
	 * @param SchedulerPresenter $presenter
	 * @return Form Scheduler configuration form
	 */
	public function create(SchedulerPresenter $presenter) {
		$id = $presenter->getParameter('id');
		$form = $this->factory->create();
		$defaults = $this->manager->load($id);
		foreach (array_keys($defaults) as $key) {
			if ($key === 'sensors') {
				$form->addTextArea($key, $key);
			} elseif ($key === 'timeout') {
				$form->addInteger($key, $key);
			} else {
				$form->addText($key, $key);
			}
		}
		$form->addSubmit('save', 'Save');
		$form->setDefaults($defaults);
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter, $id) {
			$this->manager->save($values, $id);
			$presenter->redirect('Scheduler:default');
		};
		return $form;
	}

}
