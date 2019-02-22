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

use App\ConfigModule\Models\WebSocketManager;
use App\ConfigModule\Presenters\WebsocketPresenter;
use App\CoreModule\Datagrids\DataGridFactory;
use App\CoreModule\Exceptions\NonExistingJsonSchemaException;
use Nette\IOException;
use Nette\SmartObject;
use Nette\Utils\JsonException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * WebSocket interface data grid
 */
class WebSocketDataGridFactory {

	use SmartObject;

	/**
	 * @var WebSocketManager WebSocket manager
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
	 * @param WebSocketManager $configManager WebSocket interface manager
	 */
	public function __construct(DataGridFactory $dataGridFactory, WebSocketManager $configManager) {
		$this->dataGridFactory = $dataGridFactory;
		$this->configManager = $configManager;
	}

	/**
	 * Creates the WebSocket interface data grid
	 * @param WebsocketPresenter $presenter WebSocket interface configuration presenter
	 * @param string $name Data grid's component name
	 * @return DataGrid WebSocket data grid
	 * @throws DataGridColumnStatusException
	 * @throws DataGridException
	 * @throws JsonException
	 */
	public function create(WebsocketPresenter $presenter, string $name): DataGrid {
		$this->presenter = $presenter;
		$grid = $this->dataGridFactory->create($presenter, $name);
		$grid->setDataSource($this->configManager->list());
		$grid->addColumnText('messagingInstance', 'config.websocket.form.instance');
		$grid->addColumnNumber('port', 'config.websocket.form.WebsocketPort');
		$grid->addColumnStatus('acceptAsyncMsg', 'config.websocket.form.acceptAsyncMsg')
			->addOption(true, 'config.components.form.enabled')->setIcon('ok')->endOption()
			->addOption(false, 'config.components.form.disabled')
			->setIcon('remove')->setClass('btn btn-xs btn-danger')->endOption()
			->onChange[] = [$this, 'changeAsyncMsg'];
		$grid->addColumnStatus('acceptOnlyLocalhost', 'config.websocket.form.acceptOnlyLocalhost')
			->addOption(true, 'config.components.form.enabled')->setIcon('ok')->endOption()
			->addOption(false, 'config.components.form.disabled')
			->setIcon('remove')->setClass('btn btn-xs btn-danger')->endOption()
			->onChange[] = [$this, 'changeOnlyLocalhost'];
		$grid->addAction('edit', 'config.actions.Edit')->setIcon('pencil')
			->setClass('btn btn-xs btn-info');
		$grid->addAction('delete', 'config.actions.Remove')->setIcon('remove')
			->setClass('btn btn-xs btn-danger ajax')
			->setConfirm('config.websocket.interface.messages.confirmDelete', 'messagingInstance');
		$grid->addToolbarButton('add', 'config.actions.Add')
			->setClass('btn btn-xs btn-success');
		return $grid;
	}

	/**
	 * Changes the status of the asynchronous messaging
	 * @param int $id Component ID
	 * @param bool $status New asynchronous messaging status
	 * @throws JsonException
	 */
	public function changeAsyncMsg(int $id, bool $status): void {
		$config = $this->configManager->load($id);
		$config['acceptAsyncMsg'] = $status;
		try {
			$this->configManager->save($config);
			$this->presenter->flashMessage('config.messages.success', 'success');
		} catch (IOException $e) {
			$this->presenter->flashMessage('config.messages.writeFailures.ioError', 'danger');
		} catch (NonExistingJsonSchemaException $e) {
			$this->presenter->flashMessage('config.messages.writeFailures.nonExistingJsonSchema', 'danger');
		} finally {
			if ($this->presenter->isAjax()) {
				$this->presenter->redrawControl('flashes');
				$dataGrid = $this->presenter['configWebSocketDataGrid'];
				$dataGrid->setDataSource($this->configManager->list());
				$dataGrid->redrawItem($id);
			}
		}
	}

	/**
	 * Changes the status of accepting connections only from localhost
	 * @param int $id Component ID
	 * @param bool $status New accepting connections only from localhost status
	 * @throws JsonException
	 */
	public function changeOnlyLocalhost(int $id, bool $status): void {
		$config = $this->configManager->load($id);
		$config['acceptOnlyLocalhost'] = $status;
		try {
			$this->configManager->save($config);
			$this->presenter->flashMessage('config.messages.success', 'success');
		} catch (IOException $e) {
			$this->presenter->flashMessage('config.messages.writeFailures.ioError', 'danger');
		} catch (NonExistingJsonSchemaException $e) {
			$this->presenter->flashMessage('config.messages.writeFailures.nonExistingJsonSchema', 'danger');
		} finally {
			if ($this->presenter->isAjax()) {
				$this->presenter->redrawControl('flashes');
				$dataGrid = $this->presenter['configWebSocketDataGrid'];
				$dataGrid->setDataSource($this->configManager->list());
				$dataGrid->redrawItem($id);
			}
		}
	}

}
