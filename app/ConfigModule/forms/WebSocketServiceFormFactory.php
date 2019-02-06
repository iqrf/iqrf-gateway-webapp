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
use Nette\Forms\Form;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * WebSocket service configuration service form factory
 */
class WebSocketServiceFormFactory extends GenericConfigFormFactory {

	use SmartObject;

	/**
	 * Creates the WebSocket service configuration form
	 * @param WebsocketPresenter $presenter WebSocket interface configuration presenter
	 * @return Form WebSocket service configuration form
	 * @throws JsonException
	 */
	public function create(WebsocketPresenter $presenter): Form {
		$this->manager->setComponent('shape::WebsocketCppService');
		$this->redirect = 'Websocket:default';
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('config.websocket.form'));
		$form->addText('instance', 'instance')->setRequired('messages.instance');
		$form->addInteger('WebsocketPort', 'WebsocketPort')->setRequired('messages.WebsocketPort');
		$form->addSubmit('save', 'Save');
		$form->addProtection('core.errors.form-timeout');
		$id = $presenter->getParameter('id');
		if (isset($id)) {
			$form->setDefaults($this->manager->load(intval($id)));
		}
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

}
