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
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Models\BondingManager;
use App\IqrfNetModule\Presenters\NetworkPresenter;
use Contributte\Translation\Wrappers\Message;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * IQMESH Bonding form factory
 */
class BondingFormFactory {

	use SmartObject;

	/**
	 * @var BondingManager IQMESH Bonding manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var NetworkPresenter IQMESH Network manager presenter
	 */
	private $presenter;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param BondingManager $manager IQMESH Bonding manager
	 */
	public function __construct(FormFactory $factory, BondingManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Creates IQMESH bonding form
	 * @param NetworkPresenter $presenter IQMESH Network manager presenter
	 * @return Form IQMESH bonding form
	 */
	public function create(NetworkPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create('iqrfnet.bonding');
		$form->addGroup();
		$form->addSelect('method', 'method', $this->getBondingMethods())
			->addCondition(Form::EQUAL, 'smartConnect')
			->toggle('smartConnect', true)
			->elseCondition()
			->toggle('smartConnect', false);
		$form->addInteger('address', 'address')
			->setDefaultValue(1);
		$form->addCheckbox('autoAddress', 'autoAddress');
		$form['address']->addConditionOn($form['autoAddress'], Form::EQUAL, false)
			->addRule(Form::RANGE, 'messages.address', [1, 239])
			->setRequired('messages.address');
		$form->addInteger('testRetries', 'testRetries')
			->setDefaultValue(1);
		$this->addSmartConnectInputs($form);
		$form->addCheckbox('coordinatorOnly', 'coordinatorOnly');
		$form->addProtection('core.errors.form-timeout');
		$form->addSubmit('add', 'addBond')
			->setHtmlAttribute('class', 'ajax')
			->onClick[] = [$this, 'addBond'];
		$confirmMessage = $this->presenter->getTranslator()->translate('iqrfnet.bonding.messages.remove.confirm');
		$form->addSubmit('remove', 'removeBond')
			->setHtmlAttribute('class', 'ajax')
			->setHtmlId('frm-iqrfNetBondingForm-removeBond')
			->setHtmlAttribute('data-confirm', $confirmMessage)
			->onClick[] = [$this, 'removeBond'];
		$confirmMessage = $this->presenter->getTranslator()->translate('iqrfnet.bonding.messages.clearAll.confirm');
		$form->addSubmit('clear', 'clearAllBonds')
			->setHtmlAttribute('class', 'ajax')
			->setHtmlId('frm-iqrfNetBondingForm-clearAllBonds')
			->setHtmlAttribute('data-confirm', $confirmMessage)
			->setValidationScope([])
			->onClick[] = [$this, 'clearAllBonds'];
		return $form;
	}

	/**
	 * Returns bonding methods
	 * @return mixed[] Bonding methods
	 */
	private function getBondingMethods(): array {
		$methods = ['local', 'smartConnect'];
		foreach ($methods as $id => $method) {
			$methods[$method] = 'methods.' . $method;
			unset($methods[$id]);
		}
		return $methods;
	}

	/**
	 * Add inputs fo IQRF SmartConnect
	 * @param Form $form IQMESH Bonding form
	 */
	private function addSmartConnectInputs(Form $form): void {
		$form->addGroup()->setOption('id', 'smartConnect');
		$form->addText('smartConnectCode', 'smartConnectCode')
			->addConditionOn($form['method'], Form::EQUAL, 'smartConnect')
			->setRequired('messages.smartConnectCode');
		$form->addGroup();
	}

	/**
	 * Adds a new bond
	 * @param SubmitButton $button Submit button for adding a new bond
	 */
	public function addBond(SubmitButton $button): void {
		$values = $button->getForm()->getValues();
		$address = ($values['autoAddress'] === true) ? 0 : $values['address'];
		try {
			switch ($values['method']) {
				case 'local':
					$this->manager->bondLocal($address);
					break;
				case 'smartConnect':
					$code = $values['smartConnectCode'];
					$testRetries = $values['testRetries'] ?? 1;
					$this->manager->bondSmartConnect($address, $code, $testRetries);
					break;
			}
			$this->presenter->flashSuccess('iqrfnet.bonding.messages.add.success');
		} catch (EmptyResponseException | DpaErrorException | JsonException $e) {
			$this->presenter->flashError('iqrfnet.bonding.messages.add.failure');
		}
		$this->presenter->handleShowNodes();
	}

	/**
	 * Clears all bonds
	 * @param SubmitButton $button Submit button for cleaning al bonds
	 */
	public function clearAllBonds(SubmitButton $button): void {
		$values = $button->getForm()->getValues();
		try {
			$this->manager->clearAll($values['coordinatorOnly']);
			$this->presenter->flashSuccess('iqrfnet.bonding.messages.clearAll.success');
		} catch (EmptyResponseException | DpaErrorException | JsonException $e) {
			$this->presenter->flashError('iqrfnet.bonding.messages.clearAll.failure');
		}
		$this->presenter->handleShowNodes();
	}

	/**
	 * Removes a bond
	 * @param SubmitButton $button Submit button for removing a bond
	 */
	public function removeBond(SubmitButton $button): void {
		$form = $button->getForm();
		$values = $form->getValues();
		if ($values['address'] === null) {
			$form['address']->addError('messages.address');
			return;
		}
		try {
			$this->manager->remove($values['address'], $values['coordinatorOnly']);
			$this->presenter->flashSuccess('iqrfnet.bonding.messages.remove.success');
		} catch (EmptyResponseException | DpaErrorException | JsonException $e) {
			$this->presenter->flashError('iqrfnet.bonding.messages.remove.failure');
		}
		$this->presenter->handleShowNodes();
	}

}
