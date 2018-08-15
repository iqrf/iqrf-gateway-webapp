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
declare(strict_types = 1);

namespace App\IqrfAppModule\Forms;

use App\Forms\FormFactory;
use App\IqrfAppModule\Model\EmptyResponseException;
use App\IqrfAppModule\Model\DpaErrorException;
use App\IqrfAppModule\Model\IqrfAppManager;
use App\IqrfAppModule\Presenters\SendRawPresenter;
use Nette;
use Nette\Forms\Form;

/**
 * Send raw DPA packet form factory.
 */
class SendRawFormFactory {

	use Nette\SmartObject;

	/**
	 * @var IqrfAppManager Manager for communicating with iqrfapp
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var SendRawPresenter IQRF Send DPA raw packet presenter
	 */
	private $presenter;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param IqrfAppManager $manager Manager for communicating with iqrfapp
	 */
	public function __construct(FormFactory $factory, IqrfAppManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Create IQRF App send RAW packet form
	 * @param SendRawPresenter $presenter IQRF Send DPA raw packet presenter
	 * @return Form IQRF App send RAW packet form
	 */
	public function create(SendRawPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('iqrfapp.send-packet'));
		$form->addText('packet', 'packet')->setRequired('messages.packet');
		$form->addCheckbox('overwriteAddress', 'overwriteAddress')
				->setDefaultValue(false);
		$form->addText('address', 'customAddress')->setDefaultValue('00')->setRequired(false)
				->addRule(Form::PATTERN, 'messages.address-rule', '[0-9A-Fa-f]{1,2}')
				->addRule(Form::MAX_LENGTH, 'messages.address-length', 2)
				->addConditionOn($form['overwriteAddress'], Form::EQUAL, true);
		$form->addCheckbox('timeoutEnabled', 'overwriteTimeout')
				->setDefaultValue(true);
		$form->addInteger('timeout', 'customTimeout')->setDefaultValue(1000)
				->addConditionOn($form['timeoutEnabled'], Form::EQUAL, true)
				->setRequired('customTimeout');
		$form->addSubmit('send', 'send');
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = [$this, 'onSuccess'];
		return $form;
	}

	/**
	 * Send raw DPA packet
	 * @param Form $form IQRF App send RAW packet form
	 */
	public function onSuccess(Form $form) {
		$values = $form->getValues();
		$packet = $values['packet'];
		$timeout = $values['timeoutEnabled'] ? $values['timeout'] : null;
		if ($this->manager->validatePacket($packet)) {
			if ($values['overwriteAddress']) {
				$nadr = $values['address'];
				$packet = $this->manager->updateNadr($packet, $nadr);
			}
			try {
				$response = $this->manager->sendRaw($packet, $timeout);
				$this->presenter->handleShowResponse($response);
			} catch (\Exception $e) {
				if ($e instanceof EmptyResponseException ||
						$e instanceof DpaErrorException) {
					$message = 'No response from IQRF Gateway Daemon.';
					$form->addError($message);
					$this->presenter->flashMessage($message, 'danger');
				} else {
					throw $e;
				}
			}
		} else {
			$message = 'Invalid DPA packet.';
			$form->addError($message);
			$this->presenter->flashMessage($message, 'danger');
		}
	}

}
