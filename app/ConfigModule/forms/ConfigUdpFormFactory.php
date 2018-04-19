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

declare(strict_types=1);

namespace App\ConfigModule\Forms;

use App\ConfigModule\Model\InstanceManager;
use App\ConfigModule\Presenters\UdpPresenter;
use App\Forms\FormFactory;
use Nette;
use Nette\Application\UI\Form;

class ConfigUdpFormFactory {

	use Nette\SmartObject;

	/**
	 * @var InstanceManager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * Constructor
	 * @param InstanceManager $manager
	 * @param FormFactory $factory Generic form factory
	 */
	public function __construct(InstanceManager $manager, FormFactory $factory) {
		$this->manager = $manager;
		$this->factory = $factory;
	}

	/**
	 * Create UDP configuration form
	 * @param UdpPresenter $presenter
	 * @return Form UDP configuration form
	 */
	public function create(UdpPresenter $presenter): Form {
		$form = $this->factory->create();
		$fileName = 'UdpMessaging';
		$this->manager->setFileName($fileName);
		$form->addText('Name', 'Name')->setRequired();
		$form->addCheckbox('Enabled', 'Enabled');
		$form->addInteger('RemotePort', 'RemotePort')->setRequired();
		$form->addInteger('LocalPort', 'LocalPort')->setRequired();
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
