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

use App\ConfigModule\Model\GenericManager;
use App\ConfigModule\Presenters\IqrfPresenter;
use App\Forms\FormFactory;
use Nette;
use Nette\Application\UI\Form;

class ConfigIqrfFormFactory {

	use Nette\SmartObject;

	/**
	 * @var GenericManager
	 */
	private $manager;

	/**
	 * @var FormFactory
	 */
	private $factory;

	/**
	 * Constructor
	 * @param FormFactory $factory
	 * @param GenericManager $manager
	 */
	public function __construct(FormFactory $factory, GenericManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Create IQRF configuration form
	 * @param IqrfPresenter $presenter
	 * @return Form IQRF configuration form
	 */
	public function create(IqrfPresenter $presenter) {
		$form = $this->factory->create();
		$fileName = 'IqrfInterface';
		$form->addText('IqrfInterface', 'IqrfInterface')->setRequired();
		$form->addInteger('DpaHandlerTimeout', 'DpaHandlerTimeout')->setRequired()
				->addRule(Form::MIN, 'DPA Handler timeout must be bigger than 0.', 0);
		$form->addSubmit('save', 'Save');
		$form->setDefaults($this->manager->load($fileName));
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter, $fileName) {
			$this->manager->save($fileName, $values);
			$presenter->redirect('Homepage:default');
		};
		return $form;
	}

}
