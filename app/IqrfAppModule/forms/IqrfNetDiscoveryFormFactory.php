<?php

/**
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

namespace App\IqrfAppModule\Forms;

use App\Forms\FormFactory;
use App\IqrfAppModule\Model\IqrfNetManager;
use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * IQMESH Discovery form factory.
 */
class IqrfNetDiscoveryFormFactory {

	use Nette\SmartObject;

	/**
	 * @var IqrfNetManager IQMESH Network manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param IqrfNetManager $manager IQMESH Network manager
	 */
	public function __construct(FormFactory $factory, IqrfNetManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Create Discovery form
	 * @return Form IQMESH discovery form
	 */
	public function create(): Form {
		$form = $this->factory->create();
		$form->addInteger('txPower', 'TX Power')->setDefaultValue(6)
				->addRule(Form::RANGE, 'TX Power have to be integer from 0 to 7.', [0, 7])
				->setRequired();
		$form->addInteger('maxNode', 'Max. Node Address')->setDefaultValue(239)
				->addRule(Form::RANGE, 'Max. Node Address have to be integer form 0 to 239.', [0, 239])
				->setRequired();
		$form->addSubmit('send', 'Discovery');
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = [$this, 'onSuccess'];
		return $form;
	}

	/**
	 * Run Discovery
	 * @param Form $form IQMESH discovery form
	 * @param ArrayHash $values Values from IQMESH discovery form
	 */
	public function onSuccess(Form $form, ArrayHash $values) {
		$this->manager->discovery($values['txPower'], dechex($values['maxNode']));
	}

}
