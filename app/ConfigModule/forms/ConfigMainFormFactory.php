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

use App\ConfigModule\Model\MainManager;
use App\ConfigModule\Presenters\MainPresenter;
use App\Forms\FormFactory;
use Nette;
use Nette\Application\UI\Form;

class ConfigMainFormFactory {

	use Nette\SmartObject;

	/**
	 * @var MainManager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param MainManager $manager
	 */
	public function __construct(FormFactory $factory, MainManager $manager) {
		$this->manager = $manager;
		$this->factory = $factory;
	}

	/**
	 * Create Tracer configuration form
	 * @param MainPresenter $presenter
	 * @return Form Tracer configuration form
	 */
	public function create(MainPresenter $presenter): Form {
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('config.mainForm'));
		$items = ['forwarding' => 'Modes.Forwarding', 'operational' => 'Modes.Operational', 'service' => 'Modes.Service'];
		$form->addText('Configuration', 'Configuration');
		$form->addText('ConfigurationDir', 'ConfigurationDir');
		$form->addInteger('WatchDogTimeoutMilis', 'WatchDogTimeoutMilis');
		$form->addSelect('Mode', 'Mode', $items);
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
