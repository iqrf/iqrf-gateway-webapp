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

namespace App\IqrfAppModule\Forms;

use App\Forms\FormFactory;
use App\IqrfAppModule\Model\IqrfAppManager;
use App\IqrfAppModule\Presenters\SendRawPresenter;
use Nette;
use Nette\Application\UI\Form;

class IqrfAppSendRawFormFactory {

	use Nette\SmartObject;

	/**
	 * @var IqrfAppManager
	 */
	private $manager;

	/**
	 * @var FormFactory
	 */
	private $factory;

	/**
	 * Constructor
	 * @param FormFactory $factory
	 * @param IqrfAppManager $manager
	 */
	public function __construct(FormFactory $factory, IqrfAppManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Create IQRF App send RAW packet form
	 * @param SendRawPresenter $presenter
	 * @return Form IQRF App send RAW packet form
	 */
	public function create(SendRawPresenter $presenter) {
		$form = $this->factory->create();
		$form->addText('packet', 'DPA packet')->setRequired();
		$form->addCheckbox('timeoutEnabled', 'Set own DPA timeout')
				->setDefaultValue(true);
		$form->addText('timeout', 'DPA timeout (ms)')->setDefaultValue(1000)
				->addConditionOn($form['timeoutEnabled'], Form::EQUAL, true)
				->setRequired();
		$form->addSubmit('send', 'Send');
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter) {
			$packet = $values['packet'];
			$timeout = $values['timeoutEnabled'] ? $values['timeout'] : null;
			if ($this->manager->validatePacket($packet)) {
				$response = $this->manager->sendRaw($packet, $timeout);
				$presenter->handleShowResponse($response);
			}
		};
		return $form;
	}

}
