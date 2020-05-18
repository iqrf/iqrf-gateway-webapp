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

namespace App\ConfigModule\Forms;

use App\ConfigModule\Presenters\WebsocketPresenter;
use Nette\Application\UI\Form;
use Nette\Forms\Container;

/**
 * WebSocket messaging configuration form factory
 */
class WebSocketMessagingFormFactory extends GenericConfigFormFactory {

	/**
	 * Translation prefix
	 */
	private const PREFIX = 'config.websocket.form';

	/**
	 * Creates the WebSocket messaging configuration form
	 * @param WebsocketPresenter $presenter WebSocket interface configuration presenter
	 * @return Form WebSocket messaging configuration form
	 */
	public function create(WebsocketPresenter $presenter): Form {
		$this->manager->setComponent('iqrf::WebsocketMessaging');
		$this->redirect = 'Websocket:default';
		$this->presenter = $presenter;
		$form = $this->factory->create(self::PREFIX);
		$form->addGroup();
		$form->addText('instance', 'instance')
			->setRequired('messages.messagingInstance');
		$form->addCheckbox('acceptAsyncMsg', 'acceptAsyncMsg');
		$form->addGroup('requiredInterfaces');
		$interfaces = $form->addMultiplier('RequiredInterfaces', [$this, 'addRequiredInterfaces'], 0);
		$interfaces->addRemoveButton('messages.requiredInterface.remove')
			->addClass('btn btn-danger');
		$interfaces->addCreateButton('messages.requiredInterface.add')
			->addClass('btn btn-success');
		$form->addGroup();
		$form->addSubmit('save', 'Save');
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Adds the required interfaces into the
	 * @param Container $container FOrm container
	 * @param Form $form Configuration form
	 */
	public function addRequiredInterfaces(Container $container, Form $form): void {
		$translator = $form->getTranslator();
		$prompt = 'messages.requiredInterface.name';
		$container->addSelect('name', 'requiredInterface.name')
			->setItems(['shape::IWebsocketService'], false)
			->setTranslator(null)
			->setRequired($prompt)
			->setPrompt($translator->translate($prompt));
		$instances = $this->manager->getComponentInstances('shape::WebsocketCppService');
		$prompt = 'messages.requiredInterface.instance';
		$target = $container->addContainer('target');
		$target->addSelect('instance', 'requiredInterface.instance')
			->setItems($instances, false)
			->setTranslator(null)
			->setRequired($prompt)
			->setPrompt($translator->translate($prompt));
	}

}
