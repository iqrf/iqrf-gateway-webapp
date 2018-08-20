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
use App\IqrfAppModule\Exception\EmptyResponseException;
use App\IqrfAppModule\Exception\DpaErrorException;
use App\IqrfAppModule\Model\IqrfNetManager;
use App\IqrfAppModule\Presenters\NetworkPresenter;
use Nette;
use Nette\Forms\Form;
use Nette\Forms\Controls\SubmitButton;

/**
 * IQMESH Security form factory.
 */
class SecurityFormFactory {

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
	 * @var NetworkPresenter IQMESH Network presenter
	 */
	private $presenter;

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
	 * Create IQMESH Access password form
	 * @param NetworkPresenter $presenter IQMESH Network presenter
	 * @return Form IQMESH Access password form
	 */
	public function create(NetworkPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('iqrfapp.network-manager.security'));
		$inputFormats = [
			'ASCII' => 'input-formats.ascii',
			'HEX' => 'input-formats.hex',
		];
		$form->addSelect('format', 'input-format', $inputFormats)
				->setDefaultValue('ASCII');
		$form->addText('password', 'password')
				->setRequired(false)
				->addConditionOn($form['format'], Form::EQUAL, 'ASCII')
				->addRule(Form::MAX_LENGTH, 'messages.ascii-password-length', 16)
				->elseCondition($form['format'], Form::EQUAL, 'HEX')
				->addRule(Form::PATTERN, 'messages.hex-password-format', '[0-9A-Fa-f]{0,32}')
				->addRule(Form::MAX_LENGTH, 'messages.hex-password-length', 32);
		$form->addSubmit('setAccessPassword', 'setAccessPassword')->onClick[] = [$this, 'accessPasword'];
		$form->addSubmit('setUserKey', 'setUserKey')->onClick[] = [$this, 'userKey'];
		$form->addProtection('core.errors.form-timeout');
		return $form;
	}

	/**
	 * Set IQMESH Access Password
	 * @param SubmitButton $button Submit button for setting Access Password
	 */
	public function accessPasword(SubmitButton $button) {
		$values = $button->getForm()->getValues();
		try {
			$this->manager->setSecurity($values['password'], $values['format'], IqrfNetManager::SECURITY_ACCESS_PASSOWRD);
		} catch (\Exception $e) {
			if ($e instanceof EmptyResponseException ||
					$e instanceof DpaErrorException) {
				$message = 'No response from IQRF Gateway Daemon.';
				$button->addError($message);
				$this->presenter->flashMessage($message, 'danger');
			} else {
				throw $e;
			}
		}
	}

	/**
	 * Set IQMESH User Key
	 * @param SubmitButton $button Submit button for setting User Key
	 */
	public function userKey(SubmitButton $button) {
		$values = $button->getForm()->getValues();
		try {
			$this->manager->setSecurity($values['password'], $values['format'], IqrfNetManager::SECURITY_USER_KEY);
		} catch (\Exception $e) {
			if ($e instanceof EmptyResponseException ||
					$e instanceof DpaErrorException) {
				$message = 'No response from IQRF Gateway Daemon.';
				$button->addError($message);
				$this->presenter->flashMessage($message, 'danger');
			} else {
				throw $e;
			}
		}
	}

}
