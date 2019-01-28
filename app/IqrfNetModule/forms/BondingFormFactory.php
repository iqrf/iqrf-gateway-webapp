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

namespace App\IqrfNetModule\Forms;

use App\CoreModule\Forms\FormFactory;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Models\BondingManager;
use App\IqrfNetModule\Presenters\NetworkPresenter;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
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
	 * @var NetworkPresenter IQMESH Network presenter
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
	 * Create IQMESH bonding form
	 * @param NetworkPresenter $presenter IQMESH Network presenter
	 * @return Form IQMESH bonding form
	 */
	public function create(NetworkPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$methods = ['local', 'smartConnect'];
		foreach ($methods as $id => $method) {
			$methods[$method] = 'methods.' . $method;
			unset($methods[$id]);
		}
		$form->setTranslator($form->getTranslator()->domain('iqrfnet.bonding'));
		$form->addSelect('method', 'method', $methods);
		$form->addInteger('address', 'address')->setDefaultValue(1)
			->addRule(Form::RANGE, 'messages.address', [1, 239]);
		$form->addCheckbox('autoAddress', 'autoAddress')
			->addCondition(Form::EQUAL, false)
			->toggle('frm-iqrfNetBondingForm-rebond')
			->toggle('frm-iqrfNetBondingForm-remove');
		$form['address']->addConditionOn($form['autoAddress'], Form::EQUAL, false)
			->addRule(Form::PATTERN, 'messages.address', '[0-9a-fA-F]{1,2}')
			->setRequired('messages.address');
		$form->addText('smartConnectCode', 'smartConnectCode')
			->addConditionOn($form['method'], Form::EQUAL, 'smartConnect')
			->setRequired('message.smartConnectCode');
		$form->addSubmit('bond', 'bondNode')
			->setHtmlAttribute('id', 'frm-iqrfNetBondingForm-bondNode')
			->onClick[] = [$this, 'bondNode'];
		$form->addSubmit('rebond', 'rebondNode')
			->setHtmlAttribute('id', 'frm-iqrfNetBondingForm-rebond')
			->onClick[] = [$this, 'rebondNode'];
		$form->addSubmit('remove', 'removeNode')
			->setHtmlAttribute('id', 'frm-iqrfNetBondingForm-remove')
			->onClick[] = [$this, 'removeNode'];
		$form->addSubmit('clear', 'clearAllBonds')
			->setHtmlAttribute('id', 'frm-iqrfNetBondingForm-clear')
			->onClick[] = [$this, 'clearAllBonds'];
		$form->addProtection('core.errors.form-timeout');
		return $form;
	}

	/**
	 * Bond new node
	 * @param SubmitButton $button Submit button for bonding
	 */
	public function bondNode(SubmitButton $button): void {
		$values = $button->getForm()->getValues();
		$address = $values['autoAddress'] ? 0 : $values['address'];
		try {
			switch ($values['method']) {
				case 'local':
					$this->manager->bondLocal($address);
					break;
				case 'smartConnect':
					$this->manager->bondSmartConnect($address, $values['smartConnectCode']);
					break;
			}
			$this->presenter->flashMessage('iqrfnet.bonding.messages.bond.success', 'success');
		} catch (EmptyResponseException | DpaErrorException | JsonException $e) {
			$this->presenter->flashMessage('iqrfnet.bonding.messages.bond.failure', 'danger');
		}
	}

	/**
	 * Clear all bonds
	 * @param SubmitButton $button Submit button for cleaning all bonds
	 */
	public function clearAllBonds(SubmitButton $button): void {
		try {
			$this->manager->clearAll();
			$this->presenter->flashMessage('iqrfnet.bonding.messages.clearAll.success', 'success');
		} catch (EmptyResponseException | DpaErrorException | JsonException $e) {
			$this->presenter->flashMessage('iqrfnet.bonding.messages.clearAll.failure', 'danger');
		}
	}

	/**
	 * Rebond node
	 * @param SubmitButton $button Submit button for rebonding
	 */
	public function rebondNode(SubmitButton $button): void {
		$values = $button->getForm()->getValues();
		try {
			$this->manager->rebond($values['address']);
			$this->presenter->flashMessage('iqrfnet.bonding.messages.rebond.success', 'success');
		} catch (EmptyResponseException | DpaErrorException | JsonException $e) {
			$this->presenter->flashMessage('iqrfnet.bonding.messages.rebond.failure', 'danger');
		}
	}

	/**
	 * Remove node
	 * @param SubmitButton $button Submit button for removing node
	 */
	public function removeNode(SubmitButton $button): void {
		$values = $button->getForm()->getValues();
		try {
			$this->manager->remove($values['address']);
			$this->presenter->flashMessage('iqrfnet.bonding.messages.remove.success', 'success');
		} catch (EmptyResponseException | DpaErrorException | JsonException $e) {
			$this->presenter->flashMessage('iqrfnet.bonding.messages.remove.failure', 'danger');
		}
	}

}
