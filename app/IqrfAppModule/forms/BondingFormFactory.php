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
 * IQMESH Bonding form factory.
 */
class BondingFormFactory {

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
	 * Create IQMESH bonding form
	 * @param NetworkPresenter $presenter IQMESH Network presenter
	 * @return Form IQMESH bonding form
	 */
	public function create(NetworkPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('iqrfapp.network-manager.bonding'));
		$form->addCheckbox('autoAddress', 'autoAddress');
		$form->addText('address', 'address')->setDefaultValue('01')
				->addConditionOn($form['autoAddress'], Form::EQUAL, false)
				->addRule(Form::PATTERN, 'messages.address', '[0-9a-fA-F]{1,2}')
				->setRequired('messages.address');
		$form->addSubmit('bond', 'bondNode')->onClick[] = [$this, 'bondNode'];
		$form->addSubmit('rebond', 'rebondNode')->onClick[] = [$this, 'rebondNode'];
		$form->addSubmit('remove', 'removeNode')->onClick[] = [$this, 'removeNode'];
		$form->addSubmit('clear', 'clearAllBonds')->onClick[] = [$this, 'clearAllBonds'];
		$form->addProtection('core.errors.form-timeout');
		return $form;
	}

	/**
	 * Bond new node
	 * @param SubmitButton $button Submit button for bonding
	 */
	public function bondNode(SubmitButton $button) {
		$values = $button->getForm()->getValues();
		$address = $values['autoAddress'] ? '00' : $values['address'];
		try {
			$this->manager->bondNode($address);
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
	 * Clear all bonds
	 * @param SubmitButton $button Submit button for cleaning all bonds
	 */
	public function clearAllBonds(SubmitButton $button) {
		try {
			$this->manager->clearAllBonds();
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
	 * Rebond node
	 * @param SubmitButton $button Submit button for rebonding
	 */
	public function rebondNode(SubmitButton $button) {
		$values = $button->getForm()->getValues();
		try {
			$this->manager->rebondNode($values['address']);
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
	 * Remove node
	 * @param SubmitButton $button Submit button for removing node
	 */
	public function removeNode(SubmitButton $button) {
		$values = $button->getForm()->getValues();
		try {
			$this->manager->removeNode($values['address']);
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
