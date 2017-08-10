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

namespace App\Forms;

use App\Forms\FormFactory;
use App\Model\ConfigManager;
use App\Model\ConfigParser;
use App\ConfigModule\Presenters\SchedulerPresenter;
use Nette;
use Nette\Application\UI\Form;

class ConfigSchedulerFormFactory {

	use Nette\SmartObject;

	/**
	 * @var ConfigManager
	 */
	private $configManager;

	/**
	 * @var ConfigParser
	 */
	private $configParser;

	/**
	 * @var FormFactory
	 */
	private $factory;

	/**
	 * Constructor
	 * @param FormFactory $factory
	 * @param ConfigManager $configManager
	 * @param ConfigParser $configParser
	 */
	public function __construct(FormFactory $factory, ConfigManager $configManager, ConfigParser $configParser) {
		$this->factory = $factory;
		$this->configManager = $configManager;
		$this->configParser = $configParser;
	}

	/**
	 * Create Scheduler configuration form
	 * @param SchedulerPresenter $presenter
	 * @return Form Scheduler configuration form
	 */
	public function create(SchedulerPresenter $presenter) {
		$id = $presenter->getParameter('id');
		$form = $this->factory->create();
		$fileName = 'Scheduler';
		$json = $this->configManager->read($fileName);
		$defaults = $this->configParser->schedulerToForm($json, $id);
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
			$this->configManager->saveScheduler($values, $id);
			$presenter->redirect('Scheduler:default');
		};
		return $form;
	}

}
