<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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

namespace App\IqrfNetModule\Forms;

use App\CoreModule\Forms\FormFactory;
use App\IqrfNetModule\Enums\DataFormat;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UnsupportedInputFormatException;
use App\IqrfNetModule\Models\SecurityManager;
use App\IqrfNetModule\Presenters\TrSecurityPresenter;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * IQMESH security form factory
 */
class SecurityFormFactory {

	use SmartObject;

	/**
	 * @var SecurityManager IQMESH security manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var TrSecurityPresenter IQMESH security presenter
	 */
	private $presenter;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param SecurityManager $manager IQMESH security manager
	 */
	public function __construct(FormFactory $factory, SecurityManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Creates IQMESH security form
	 * @param TrSecurityPresenter $presenter IQMESH security presenter
	 * @return Form IQMESH security form
	 */
	public function create(TrSecurityPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create('iqrfnet.security');
		$inputFormats = [
			DataFormat::ASCII => 'input-formats.ascii',
			DataFormat::HEX => 'input-formats.hex',
		];
		$form->addSelect('format', 'input-format', $inputFormats)
			->setDefaultValue(DataFormat::ASCII);
		$form->addText('password', 'password')
			->setRequired(false)
			->addConditionOn($form['format'], Form::EQUAL, DataFormat::ASCII)
			->addRule(Form::MAX_LENGTH, 'messages.ascii-password-length', 16)
			->endCondition()
			->addConditionOn($form['format'], Form::EQUAL, DataFormat::HEX)
			->addRule(Form::PATTERN, 'messages.hex-password-format', '[0-9A-Fa-f]{0,32}')
			->addRule(Form::MAX_LENGTH, 'messages.hex-password-length', 32);
		$form->addSubmit('setAccessPassword', 'setAccessPassword')->onClick[] = [$this, 'accessPassword'];
		$form->addSubmit('setUserKey', 'setUserKey')->onClick[] = [$this, 'userKey'];
		$form->addProtection('core.errors.form-timeout');
		return $form;
	}

	/**
	 * Sets IQMESH Access Password
	 * @param SubmitButton $button Submit button for setting Access Password
	 */
	public function accessPassword(SubmitButton $button): void {
		$values = $button->getForm()->getValues();
		try {
			$address = $this->presenter->getParameter('address', 0);
			$this->manager->setAccessPassword($address, $values['password'], $values['format']);
			$this->presenter->flashSuccess('iqrfnet.security.accessPassword.success');
		} catch (DpaErrorException | EmptyResponseException | JsonException | UnsupportedInputFormatException $e) {
			$this->presenter->flashError('iqrfnet.security.accessPassword.failure');
		}
	}

	/**
	 * Sets IQMESH User Key
	 * @param SubmitButton $button Submit button for setting User Key
	 */
	public function userKey(SubmitButton $button): void {
		$values = $button->getForm()->getValues();
		try {
			$address = $this->presenter->getParameter('address', 0);
			$this->manager->setUserKey($address, $values['password'], $values['format']);
			$this->presenter->flashSuccess('iqrfnet.security.userKey.success');
		} catch (DpaErrorException | EmptyResponseException | JsonException | UnsupportedInputFormatException $e) {
			$this->presenter->flashError('iqrfnet.security.userKey.failure');
		}
	}

}
