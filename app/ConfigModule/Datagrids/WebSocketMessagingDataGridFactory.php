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
 * WebSocket messaging data grid
 */
class WebSocketMessagingDataGridFactory {

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
	 * Creates the WebSocket messaging data grid
	 * @param WebsocketPresenter $presenter WebSocket configuration presenter
	 * @param string $name Data grid's component name
	 * @return DataGrid WebSocket messaging data grid
	 * @throws DataGridColumnStatusException
	 * @throws DataGridException
	 * @throws JsonException
	 */
	public function create(WebsocketPresenter $presenter, string $name): DataGrid {
		$this->presenter = $presenter;
		$grid = $this->dataGridFactory->create($presenter, $name);
		$grid->setDataSource($this->getData());
		$grid->addColumnText('instance', 'config.websocket.form.instance');
		$grid->addColumnStatus('acceptAsyncMsg', 'config.websocket.form.acceptAsyncMsg')
			->addOption(true, 'config.components.form.enabled')
			->setIcon('ok')
			->endOption()
			->addOption(false, 'config.components.form.disabled')
			->setIcon('remove')
			->setClass('btn btn-xs btn-danger')
			->endOption()
			->onChange[] = [$this, 'changeAsyncMsg'];
		$grid->addColumnText('requiredInterfaces', 'config.websocket.form.requiredInterface.instance');
		$grid->addAction('edit-messaging', 'config.actions.Edit')
			->setIcon('pencil')
			->setClass('btn btn-xs btn-info');
		$grid->addAction('delete-messaging', 'config.actions.Remove')
			->setIcon('remove')
			->setClass('btn btn-xs btn-danger ajax')
			->setConfirmation(new StringConfirmation('config.websocket.messaging.messages.confirmDelete', 'instance'));
		$grid->addToolbarButton('add-messaging', 'config.actions.Add')
			->setClass('btn btn-xs btn-success')
			->setIcon('plus');
		return $grid;
	}

	/**
	 * Parses the data for this data grid
	 * @return mixed[] Data for data grid
	 * @throws JsonException
	 */
	private function getData(): array {
		$this->configManager->setComponent('iqrf::WebsocketMessaging');
		$configurations = $this->configManager->list();
		foreach ($configurations as &$configuration) {
			$requiredInterfaces = '';
			foreach ($configuration['RequiredInterfaces'] as $interfaces) {
				$requiredInterfaces .= $interfaces['target']['instance'] . ', ';
			}
			$configuration['requiredInterfaces'] = trim($requiredInterfaces, ', ');
		}
		return $configurations;
	}

	/**
	 * Changes the status of the asynchronous messaging
	 * @param string $id Component ID
	 * @param string $status New asynchronous messaging status
	 * @throws JsonException
	 */
	public function changeAsyncMsg(string $id, string $status): void {
		$config = $this->configManager->load((int) $id);
		$config['acceptAsyncMsg'] = boolval($status);
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
				$dataGrid = $this->presenter['configWebSocketMessagingDataGrid'];
				$dataGrid->setDataSource($this->getData());
				$dataGrid->reloadTheWholeGrid();
			}
		}
	}

}
