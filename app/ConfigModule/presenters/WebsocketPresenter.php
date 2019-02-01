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

use App\ConfigModule\Datagrids\WebSocketDataGridFactory;
use App\ConfigModule\Datagrids\WebSocketMessagingDataGridFactory;
use App\ConfigModule\Datagrids\WebSocketServiceDataGridFactory;
use App\ConfigModule\Forms\WebSocketFormFactory;
use App\ConfigModule\Forms\WebSocketMessagingFormFactory;
use App\ConfigModule\Forms\WebSocketServiceFormFactory;
use App\ConfigModule\Models\GenericManager;
use App\ConfigModule\Models\WebSocketManager;
use Nette\Forms\Form;
use Nette\Utils\JsonException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * WebSocket interface configuration presenter
 */
class WebsocketPresenter extends GenericPresenter {

	/**
	 * @var WebSocketDataGridFactory WebSocket interface data grid factory
	 * @inject
	 */
	public $basicDataGridFactory;

	/**
	 * @var WebSocketFormFactory WebSocket instance configuration form factory
	 * @inject
	 */
	public $basicFormFactory;

	/**
	 * @var WebSocketMessagingDataGridFactory WebSocket messaging configuration data grid factory
	 * @inject
	 */
	public $messagingDataGridFactory;

	/**
	 * @var WebSocketMessagingFormFactory WebSocket messaging configuration form factory
	 * @inject
	 */
	public $messagingFormFactory;

	/**
	 * @var WebSocketServiceDataGridFactory WebSocket service configuration data grid
	 * @inject
	 */
	public $serviceDataGridFactory;

	/**
	 * @var WebSocketServiceFormFactory WebSocket service configuration form factory
	 * @inject
	 */
	public $serviceFormFactory;

	/**
	 * @var string[] WebSocket components
	 */
	protected $components = [
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
		parent::__construct($this->components, $genericManager);
	}

	/**
	 * Edit WebSocket interface
	 * @param int $id ID of WebSocket interface
	 */
	public function renderEdit(int $id): void {
		$this->template->id = $id;
	}

	/**
	 * Edit WebSocket messaging
	 * @param int $id ID of WebSocket messaging
	 */
	public function renderEditMessaging(int $id): void {
		$this->configManager->setComponent($this->components['messaging']);
		$this->template->id = $id;
	}

	/**
	 * Edit Websocket service
	 * @param int $id ID of WebSocket service
	 */
	public function renderEditService(int $id): void {
		$this->configManager->setComponent($this->components['service']);
		$this->template->id = $id;
	}

	/**
	 * Delete WebSocket interface
	 * @param int $id ID of WebSocket interface
	 * @throws JsonException
	 */
	public function actionDelete(int $id): void {
		$this->webSocketManager->delete($id);
		$this->redirect('Websocket:default');
		$this->setView('default');
	}

	/**
	 * Delete WebSocket messaging
	 * @param int $id ID of WebSocket messaging
	 * @throws JsonException
	 */
	public function actionDeleteMessaging(int $id): void {
		$this->configManager->setComponent($this->components['messaging']);
		$this->configManager->delete($id);
		$this->redirect('Websocket:default');
		$this->setView('default');
	}

	/**
	 * Delete WebSocket service
	 * @param int $id ID of WebSocket service
	 * @throws JsonException
	 */
	public function actionDeleteService(int $id): void {
		$this->configManager->setComponent($this->components['service']);
		$this->configManager->delete($id);
		$this->redirect('Websocket:default');
		$this->setView('default');
	}

	/**
	 * Create WebSocket interfaces data grid
	 * @param string $name Data grid's component name
	 * @return DataGrid WebSocket interfaces data grid
	 * @throws DataGridColumnStatusException
	 * @throws DataGridException
	 * @throws JsonException
	 */
	protected function createComponentConfigWebSocketDataGrid(string $name): DataGrid {
		return $this->basicDataGridFactory->create($this, $name);
	}

	/**
	 * Create WebSocket interface form
	 * @return Form WebSocket interface form
	 * @throws JsonException
	 */
	protected function createComponentConfigWebSocketForm(): Form {
		return $this->basicFormFactory->create($this);
	}

	/**
	 * Create WebSocket messaging data grid
	 * @param string $name Data grid's component name
	 * @return DataGrid WebSocket messaging data grid
	 * @throws DataGridColumnStatusException
	 * @throws DataGridException
	 * @throws JsonException
	 */
	protected function createComponentConfigWebSocketMessagingDataGrid(string $name): DataGrid {
		return $this->messagingDataGridFactory->create($this, $name);
	}

	/**
	 * Create WebSocket messaging form
	 * @return Form WebSocket messaging form
	 * @throws JsonException
	 */
	protected function createComponentConfigWebSocketMessagingForm(): Form {
		return $this->messagingFormFactory->create($this);
	}

	/**
	 * Create WebSocket service data grid
	 * @param string $name Data grid's component name
	 * @return DataGrid WebSocket service data grid
	 * @throws JsonException
	 * @throws DataGridException
	 */
	protected function createComponentConfigWebSocketServiceDataGrid(string $name): DataGrid {
		return $this->serviceDataGridFactory->create($this, $name);
	}

	/**
	 * Create WebSocket service form
	 * @return Form WebSocket service form
	 * @throws JsonException
	 */
	protected function createComponentConfigWebSocketServiceForm(): Form {
		return $this->serviceFormFactory->create($this);
	}

}
