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
use App\IqrfNetModule\Models\DpaRawManager;
use App\IqrfNetModule\Presenters\SendRawPresenter;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * Send DPA packet form factory
 */
class SendRawFormFactory {

	use SmartObject;

	/**
	 * @var DpaRawManager JSON DPA request and response manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var SendRawPresenter Send DPA packet presenter
	 */
	private $presenter;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param DpaRawManager $manager JSON DPA request and response manager
	 */
	public function __construct(FormFactory $factory, DpaRawManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Creates send DPA packet form
	 * @param SendRawPresenter $presenter Send DPA packet presenter
	 * @return Form Send DPA packet form
	 */
	public function create(SendRawPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create('iqrfnet.send-packet');
		$form->addText('packet', 'packet')
			->setRequired('messages.packet');
		$form->addCheckbox('overwriteAddress', 'overwriteAddress')
			->setDefaultValue(false);
		$form->addInteger('address', 'customAddress')
			->setDefaultValue(0)
			->setRequired(false)
			->addConditionOn($form['overwriteAddress'], Form::EQUAL, true)
			->addRule(Form::RANGE, 'messages.address', [0, 239])
			->setRequired('messages.address');
		$form->addCheckbox('timeoutEnabled', 'overwriteTimeout')
			->setDefaultValue(false);
		$form->addInteger('timeout', 'customTimeout')
			->setDefaultValue(1000)
			->addConditionOn($form['timeoutEnabled'], Form::EQUAL, true)
			->setRequired('customTimeout');
		$form->addSubmit('send', 'send');
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = [$this, 'onSuccess'];
		return $form;
	}

	/**
	 * Sends DPA packet
	 * @param Form $form Send DPA packet form
	 * @throws JsonException
	 */
	public function onSuccess(Form $form): void {
		$values = $form->getValues();
		$packet = $values->packet;
		$timeout = ($values->timeoutEnabled === true) ? $values->timeout : null;
		if (!$this->manager->validatePacket($packet)) {
			$this->presenter->flashError('iqrfnet.send-packet.messages.invalidPacket');
			return;
		}
		if ($values->overwriteAddress === true) {
			$nadr = dechex($values->address);
			$this->manager->updateNadr($packet, $nadr);
		}
		try {
			$response = $this->manager->send($packet, $timeout);
			$this->presenter->handleShowResponse($response);
			$this->presenter->flashSuccess('iqrfnet.send-packet.messages.success');
		} catch (EmptyResponseException | DpaErrorException $e) {
			$this->presenter->flashError('iqrfnet.webSocketClient.messages.emptyResponse');
		}
	}

}
