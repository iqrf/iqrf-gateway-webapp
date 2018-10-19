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
use App\IqrfNetModule\Model\IqrfNetManager;
use App\IqrfNetModule\Presenters\NetworkPresenter;
use Nette\Forms\Form;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;
use Nette\Utils\JsonException;

/**
 * IQMESH Discovery form factory
 */
class DiscoveryFormFactory {

	use SmartObject;

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
	 * Create Discovery form
	 * @param NetworkPresenter $presenter IQMESH Network presenter
	 * @return Form IQMESH discovery form
	 */
	public function create(NetworkPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('iqrfapp.network-manager.discovery'));
		$form->addInteger('txPower', 'txPower')->setDefaultValue(6)
			->addRule(Form::RANGE, 'messages.txPower', [0, 7])
			->setRequired('messages.txPower');
		$form->addInteger('maxNode', 'maxNodeAddress')->setDefaultValue(239)
			->addRule(Form::RANGE, 'messages.maxNodeAddress', [0, 239])
			->setRequired('messages.maxNodeAddress');
		$form->addSubmit('send', 'send');
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = [$this, 'onSuccess'];
		return $form;
	}

	/**
	 * Run Discovery
	 * @param Form $form IQMESH discovery form
	 * @param ArrayHash $values Values from IQMESH discovery form
	 * @throws JsonException
	 */
	public function onSuccess(Form $form, ArrayHash $values): void {
		try {
			$this->manager->discovery($values['txPower'], dechex($values['maxNode']));
		} catch (EmptyResponseException | DpaErrorException $e) {
			$message = 'No response from IQRF Gateway Daemon.';
			$form->addError($message);
			$this->presenter->flashMessage($message, 'danger');
		}
	}

}
