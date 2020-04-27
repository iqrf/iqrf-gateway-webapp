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

namespace App\ConfigModule\Datagrids;

use App\ConfigModule\Models\GenericManager;
use App\ConfigModule\Presenters\WebsocketPresenter;
use App\CoreModule\Datagrids\DataGridFactory;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use Nette\IOException;
use Nette\SmartObject;
use Nette\Utils\JsonException;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * WebSocket service data grid
 */
class WebSocketServiceDataGridFactory {

	use SmartObject;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $configManager;

	/**
	 * @var DataGridFactory Data grid factory
	 */
	private $dataGridFactory;

	/**
	 * @var WebsocketPresenter WebSocket interface configuration presenter
	 */
	private $presenter;

	/**
	 * Constructor
	 * @param DataGridFactory $dataGridFactory Generic data grid factory
	 * @param GenericManager $configManager Generic configuration manager
	 */
	public function __construct(DataGridFactory $dataGridFactory, GenericManager $configManager) {
		$this->dataGridFactory = $dataGridFactory;
		$this->configManager = $configManager;
	}

	/**
	 * Creates the WebSocket service data grid
	 * @param WebsocketPresenter $presenter WebSocket interface configuration presenter
	 * @param string $name Data grid's component name
	 * @return DataGrid WebSocket service data grid
	 * @throws DataGridException
	 * @throws JsonException
	 * @throws DataGridColumnStatusException
	 */
	public function create(WebsocketPresenter $presenter, string $name): DataGrid {
		$this->presenter = $presenter;
		$grid = $this->dataGridFactory->create($presenter, $name);
		$this->configManager->setComponent('shape::WebsocketCppService');
		$grid->setDataSource($this->list());
		$grid->addColumnText('instance', 'config.websocket.form.instance');
		$grid->addColumnNumber('WebsocketPort', 'config.websocket.form.WebsocketPort');
		$grid->addColumnStatus('acceptOnlyLocalhost', 'config.websocket.form.acceptOnlyLocalhost')
			->addOption(true, 'config.components.form.enabled')
			->setIcon('ok')
			->endOption()
			->addOption(false, 'config.components.form.disabled')
			->setIcon('remove')
			->setClass('btn btn-xs btn-danger')
			->endOption()
			->onChange[] = [$this, 'changeOnlyLocalhost'];
		$grid->addAction('edit-service', 'config.actions.Edit')
			->setIcon('pencil')
			->setClass('btn btn-xs btn-info');
		$grid->addAction('delete-service', 'config.actions.Remove')
			->setIcon('remove')
			->setClass('btn btn-xs btn-danger ajax')
			->setConfirmation(new StringConfirmation('config.websocket.service.messages.confirmDelete', 'instance'));
		$grid->addToolbarButton('add-service', 'config.actions.Add')
			->setClass('btn btn-xs btn-success')
			->setIcon('plus');
		return $grid;
	}

	/**
	 * Changes the status of accepting connections only from localhost
	 * @param string $id Component ID
	 * @param string $status New accepting connections only from localhost status
	 * @throws JsonException
	 */
	public function changeOnlyLocalhost(string $id, string $status): void {
		$config = $this->configManager->load((int) $id);
		$config['acceptOnlyLocalhost'] = boolval($status);
		try {
			$this->configManager->save($config);
			$this->presenter->flashSuccess('config.messages.success');
		} catch (IOException $e) {
			$this->presenter->flashError('config.messages.writeFailures.ioError');
		} catch (NonexistentJsonSchemaException $e) {
			$this->presenter->flashError('config.messages.writeFailures.nonExistingJsonSchema');
		} finally {
			if ($this->presenter->isAjax()) {
				$this->presenter->redrawControl('flashes');
				$dataGrid = $this->presenter['configWebSocketServiceDataGrid'];
				$dataGrid->setDataSource($this->configManager->list());
				$dataGrid->reloadTheWholeGrid();
			}
		}
	}

	/**
	 * Lists all available Websocket services
	 * @return mixed[] Available Websocket services
	 * @throws JsonException
	 */
	private function list(): array {
		$services = $this->configManager->list();
		foreach ($services as &$service) {
			if (!array_key_exists('acceptOnlyLocalhost', $service)) {
				$service['acceptOnlyLocalhost'] = false;
			}
		}
		return $services;
	}

}
