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
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * WebSocket messaging configuration form factory
 */
class WebSocketMessagingFormFactory extends GenericConfigFormFactory {

	use SmartObject;

	/**
	 * Creates the WebSocket messaging configuration form
	 * @param WebsocketPresenter $presenter WebSocket interface configuration presenter
	 * @return Form WebSocket messaging configuration form
	 * @throws JsonException
	 */
	public function create(WebsocketPresenter $presenter): Form {
		$this->manager->setComponent('iqrf::WebsocketMessaging');
		$this->redirect = 'Websocket:default';
		$this->presenter = $presenter;
		$form = $this->factory->create('config.websocket.form');
		$defaults = $this->loadData($presenter);
		$form->addText('instance', 'instance')
			->setRequired('messages.messagingInstance');
		$form->addCheckbox('acceptAsyncMsg', 'acceptAsyncMsg');
		$this->addRequiredInterfaces($form, $defaults);
		$form->addSubmit('save', 'Save');
		$form->addProtection('core.errors.form-timeout');
		$form->setDefaults($defaults);
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Loads the configuration data for the form
	 * @param WebsocketPresenter $presenter WebSocket configuration presenter
	 * @return mixed[] Configuration in an array
	 * @throws JsonException
	 */
	public function loadData(WebsocketPresenter $presenter): array {
		$data = [];
		$id = $presenter->getParameter('id');
		if (isset($id)) {
			$data = $this->manager->load(intval($id));
		}
		if (!isset($id) || $data === []) {
			$data = ['RequiredInterfaces' => [['name' => 'shape::IWebsocketService', 'target' => ['instance' => '']]]];
		}
		return $data;
	}

	/**
	 * Adds the required interfaces into the form
	 * @param Form $form Configuration form
	 * @param mixed[] $data Configuration data
	 * @throws JsonException
	 */
	private function addRequiredInterfaces(Form $form, array &$data): void {
		$translator = $this->factory->getTranslator();
		$requiredInterfaces = $form->addContainer('RequiredInterfaces');
		foreach ($data['RequiredInterfaces'] as $interfaceId => $requiredInterface) {
			$container = $requiredInterfaces->addContainer($interfaceId);
			$container->addSelect('name', 'config.websocket.form.requiredInterface.name')
				->setItems(['shape::IWebsocketService'], false)
				->setTranslator($translator)
				->setRequired('messages.requiredInterface.name');
			$target = $container->addContainer('target');
			$target->addSelect('instance', 'config.websocket.form.requiredInterface.instance')
				->setItems($this->manager->getComponentInstances('shape::WebsocketCppService'), false)
				->setTranslator($translator)
				->setRequired('messages.requiredInterface.instance');
			if ($requiredInterface['target']['instance'] === '') {
				unset($data['RequiredInterfaces'][$interfaceId]['target']['instance']);
			}
		}
	}

}
