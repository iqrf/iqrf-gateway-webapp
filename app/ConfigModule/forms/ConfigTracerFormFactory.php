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

use App\ConfigModule\Presenters\TracerPresenter;
use App\Forms\FormFactory;
use App\Model\ConfigManager;
use Nette;
use Nette\Application\UI\Form;

class ConfigTracerFormFactory {

	use Nette\SmartObject;

	/**
	 * @var ConfigManager
	 */
	private $configManager;

	/**
	 * @var FormFactory
	 */
	private $factory;

	/**
	 * Constructor
	 * @param FormFactory $factory
	 * @param ConfigManager $configManager
	 */
	public function __construct(FormFactory $factory, ConfigManager $configManager) {
		$this->factory = $factory;
		$this->configManager = $configManager;
	}

	/**
	 * Create Tracer configuration form
	 * @param TracerPresenter $presenter
	 * @return Form Tracer configuration form
	 */
	public function create(TracerPresenter $presenter) {
		$form = $this->factory->create();
		$fileName = 'TracerFile';
		$json = $this->configManager->read($fileName);
		$items = ['err' => 'Error', 'war' => 'Warning', 'inf' => 'Info', 'dbg' => 'Debug'];
		$form->addText('TraceFileName', 'TraceFileName');
		$form->addInteger('TraceFileSize', 'TraceFileSize');
		$form->addSelect('VerbosityLevel', 'VerbosityLevel', $items);
		$form->addSubmit('save', 'Save');
		$form->setDefaults($json);
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter, $fileName) {
			$this->configManager->write($fileName, $values);
			$presenter->redirect('Homepage:default');
		};
		return $form;
	}

}
