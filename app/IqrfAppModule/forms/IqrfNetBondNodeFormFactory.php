<?php

/**
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

namespace App\IqrfAppModule\Forms;

use App\Forms\FormFactory;
use App\IqrfAppModule\Model\IqrfNetManager;
use App\IqrfAppModule\Presenters\NetworkPresenter;
use Nette;
use Nette\Application\UI\Form;

class IqrfNetBondNodeFormFactory {

	use Nette\SmartObject;

	/**
	 * @var IqrfNetManager
	 */
	private $manager;

	/**
	 * @var FormFactory
	 */
	private $factory;

	/**
	 * Constructor
	 * @param FormFactory $factory
	 * @param IqrfNetManager $manager
	 */
	public function __construct(FormFactory $factory, IqrfNetManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Create IQRF App send RAW packet form
	 * @param NetworkPresenter $presenter
	 * @return Form IQRF App send RAW packet form
	 */
	public function create(NetworkPresenter $presenter) {
		$form = $this->factory->create();
		$form->addCheckbox('autoAddress', 'Auto address')
				->setDefaultValue(true);
		$form->addText('address', 'Address (HEX)')->setDefaultValue('01')
				->addConditionOn($form['autoAddress'], Form::EQUAL, false)
				->setRequired();
		$form->addSubmit('send', 'Bond Node');
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter) {
			$address = $values['autoAddress'] ? '00' : $values['address'];
			$this->manager->bondNode($address);
		};
		return $form;
	}

}
