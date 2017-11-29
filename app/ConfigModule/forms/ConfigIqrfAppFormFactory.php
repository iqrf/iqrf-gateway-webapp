<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017 IQRF Tech s.r.o.
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
use App\ConfigModule\Presenters\IqrfAppPresenter;
use App\Forms\FormFactory;
use Nette;
use Nette\Application\UI\Form;

class ConfigIqrfAppFormFactory {

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
	 * @param IqrfAppPresenter $presenter
	 * @return Form IQRF configuration form
	 */
	public function create(IqrfAppPresenter $presenter) {
		$form = $this->factory->create();
		$fileName = 'iqrfapp';
		$items = ['err' => 'Error', 'war' => 'Warning', 'inf' => 'Info', 'dbg' => 'Debug'];
		$this->manager->setFileName($fileName);
		$form->addText('LocalMqName', 'LocalMqName')->setRequired();
		$form->addText('RemoteMqName', 'RemoteMqName')->setRequired();
		$form->addInteger('DefaultTimeout', 'DefaultTimeout')->setRequired();
		$form->addSelect('VerbosityLevel', 'VerbosityLevel', $items);
		$form->addSubmit('save', 'Save');
		$form->setDefaults($this->manager->load());
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter) {
			$this->manager->save($values);
			$presenter->redirect('Homepage:default');
		};
		return $form;
	}

}
