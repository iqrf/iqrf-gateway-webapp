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
use App\IqrfNetModule\Models\DiscoveryManager;
use App\IqrfNetModule\Presenters\NetworkPresenter;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * IQMESH Discovery form factory
 */
class DiscoveryFormFactory {

	use SmartObject;

	/**
	 * @var DiscoveryManager IQMESH Discovery manager
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
	 * @param DiscoveryManager $manager IQMESH Discovery manager
	 */
	public function __construct(FormFactory $factory, DiscoveryManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Creates IQMESH discovery form
	 * @param NetworkPresenter $presenter IQMESH Network manager presenter
	 * @return Form IQMESH discovery form
	 */
	public function create(NetworkPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create('iqrfnet.discovery');
		$form->addInteger('txPower', 'txPower')
			->setDefaultValue(6)
			->addRule(Form::RANGE, 'messages.txPower', [0, 7])
			->setRequired('messages.txPower');
		$form->addInteger('maxNode', 'maxNodeAddress')
			->setDefaultValue(239)
			->addRule(Form::RANGE, 'messages.maxNodeAddress', [0, 239])
			->setRequired('messages.maxNodeAddress');
		$form->addSubmit('send', 'send')
			->setHtmlAttribute('class', 'ajax');
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = [$this, 'onSuccess'];
		return $form;
	}

	/**
	 * Runs IQMESH discovery
	 * @param Form $form IQMESH discovery form
	 */
	public function onSuccess(Form $form): void {
		try {
			$values = $form->getValues();
			$this->manager->run($values['txPower'], $values['maxNode']);
			$this->presenter->flashSuccess('iqrfnet.discovery.messages.success');
		} catch (EmptyResponseException | DpaErrorException | JsonException $e) {
			$this->presenter->flashError('iqrfnet.discovery.messages.failure');
		}
	}

}
