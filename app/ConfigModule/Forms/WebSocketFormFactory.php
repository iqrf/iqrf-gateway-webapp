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

use App\ConfigModule\Models\WebSocketManager;
use App\ConfigModule\Presenters\WebsocketPresenter;
use App\CoreModule\Exceptions\NonExistingJsonSchemaException;
use App\CoreModule\Forms\FormFactory;
use Nette\Application\UI\Form;
use Nette\IOException;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * WebSocket interface configuration form factory
 */
class WebSocketFormFactory {

	use SmartObject;

	/**
	 * @var WebSocketManager WebSocket configuration manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var WebsocketPresenter WebSocket interface presenter
	 */
	private $presenter;

	/**
	 * Constructor
	 * @param WebSocketManager $manager WebSocket configuration manager
	 * @param FormFactory $factory Generic form factory
	 */
	public function __construct(WebSocketManager $manager, FormFactory $factory) {
		$this->manager = $manager;
		$this->factory = $factory;
	}

	/**
	 * Creates the WebSocket interface configuration form
	 * @param WebsocketPresenter $presenter WebSocket interface configuration presenter
	 * @return Form WebSocket interface configuration form
	 * @throws JsonException
	 */
	public function create(WebsocketPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create('config.websocket.form');
		$form->addInteger('port', 'WebsocketPort')
			->setRequired('messages.WebsocketPort');
		$form->addCheckbox('acceptOnlyLocalhost', 'acceptOnlyLocalhost');
		$form->addCheckbox('acceptAsyncMsg', 'acceptAsyncMsg');
		$form->addSubmit('save', 'Save');
		$form->addProtection('core.errors.form-timeout');
		$parameter = $presenter->getParameter('id');
		if (isset($parameter)) {
			$id = intval($parameter);
			if (array_key_exists($id, $this->manager->list())) {
				$form->setDefaults($this->manager->load($id));
			}
		}
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Saves the WebSocket interface configuration
	 * @param Form $form WebSocket interface configuration form
	 * @throws JsonException
	 */
	public function save(Form $form): void {
		try {
			$this->manager->save($form->getValues('array'));
			$this->presenter->flashSuccess('config.messages.success');
		} catch (NonExistingJsonSchemaException $e) {
			$this->presenter->flashError('config.messages.writeFailures.nonExistingJsonSchema');
		} catch (IOException $e) {
			$this->presenter->flashError('config.messages.writeFailures.ioError');
		} finally {
			$this->presenter->redirect('Websocket:default');
		}
	}

}
