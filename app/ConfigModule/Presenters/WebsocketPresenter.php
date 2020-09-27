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

namespace App\ConfigModule\Presenters;

use App\ConfigModule\Forms\WebSocketFormFactory;
use App\ConfigModule\Forms\WebSocketMessagingFormFactory;
use App\ConfigModule\Models\GenericManager;
use App\ConfigModule\Models\WebSocketManager;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use Nette\Application\UI\Form;
use Nette\InvalidArgumentException;
use Nette\IOException;
use Nette\Utils\JsonException;

/**
 * WebSocket interface configuration presenter
 */
class WebsocketPresenter extends GenericPresenter {

	/**
	 * @var WebSocketFormFactory WebSocket instance configuration form factory
	 * @inject
	 */
	public $basicFormFactory;

	/**
	 * @var WebSocketMessagingFormFactory WebSocket messaging configuration form factory
	 * @inject
	 */
	public $messagingFormFactory;

	/**
	 * WebSocket components
	 */
	protected const COMPONENTS = [
		'messaging' => 'iqrf::WebsocketMessaging',
		'service' => 'shape::WebsocketCppService',
	];

	/**
	 * @var WebSocketManager WebSocket manager
	 */
	private $webSocketManager;

	/**
	 * Constructor
	 * @param GenericManager $genericManager Generic configuration manager
	 * @param WebSocketManager $webSocketManager Websocket configuration manager
	 */
	public function __construct(GenericManager $genericManager, WebSocketManager $webSocketManager) {
		$this->webSocketManager = $webSocketManager;
		parent::__construct($genericManager);
	}

	/**
	 * Edits the WebSocket interface
	 * @param int $id ID of WebSocket interface
	 */
	public function actionEdit(int $id): void {
		$redirect = 'Websocket:default';
		try {
			$configuration = $this->webSocketManager->load($id);
			if ($configuration === []) {
				$this->flashError('config.messages.readFailures.notFound');
				$this->redirect('Websocket:default');
			}
			$this['configWebSocketForm']->setDefaults($configuration);
		} catch (NonexistentJsonSchemaException $e) {
			$this->flashError('config.messages.readFailures.nonExistingJsonSchema');
			$this->redirect($redirect);
		} catch (IOException $e) {
			$this->flashError('config.messages.readFailures.ioError');
			$this->redirect($redirect);
		} catch (JsonException $e) {
			$this->flashError('config.messages.readFailures.invalidJson');
			$this->redirect($redirect);
		} catch (InvalidArgumentException $e) {
			$this->flashError('config.messages.readFailures.notFound');
			$this->redirect($redirect);
		}
	}

	/**
	 * Adds a new instance of WebSocket messaging
	 */
	public function actionAddMessaging(): void {
		$this->manager->setComponent(self::COMPONENTS['messaging']);
		$defaults = ['RequiredInterfaces' => [['name' => 'shape::IWebsocketService', 'target' => ['instance' => null]]]];
		$this['configWebSocketMessagingForm']->setDefaults($defaults);
	}

	/**
	 * Edits the WebSocket messaging
	 * @param int $id ID of WebSocket messaging
	 */
	public function actionEditMessaging(int $id): void {
		$this->loadFormConfiguration($this['configWebSocketMessagingForm'], self::COMPONENTS['messaging'], $id, 'Websocket:default');
	}

	/**
	 * Deletes the WebSocket messaging
	 * @param int $id ID of WebSocket messaging
	 */
	public function actionDeleteMessaging(int $id): void {
		$this->deleteInstance(self::COMPONENTS['messaging'], $id, 'Websocket:default');
	}

	/**
	 * Creates the WebSocket interface configuration form
	 * @return Form WebSocket interface configuration form
	 */
	protected function createComponentConfigWebSocketForm(): Form {
		return $this->basicFormFactory->create($this);
	}

	/**
	 * Creates the WebSocket messaging configuration form
	 * @return Form WebSocket messaging configuration form
	 */
	protected function createComponentConfigWebSocketMessagingForm(): Form {
		return $this->messagingFormFactory->create($this);
	}

}
