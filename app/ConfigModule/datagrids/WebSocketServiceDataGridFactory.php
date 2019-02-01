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
use Nette\SmartObject;
use Nette\Utils\JsonException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * Render a WebSocket service data grid
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
	 * Constructor
	 * @param DataGridFactory $dataGridFactory Generic data grid factory
	 * @param GenericManager $configManager Generic configuration manager
	 */
	public function __construct(DataGridFactory $dataGridFactory, GenericManager $configManager) {
		$this->dataGridFactory = $dataGridFactory;
		$this->configManager = $configManager;
	}

	/**
	 * Create WebSocket service data grid
	 * @param WebsocketPresenter $presenter WebSocket configuration presenter
	 * @param string $name Data grid's component name
	 * @return DataGrid WebSocket service data grid
	 * @throws DataGridException
	 * @throws JsonException
	 */
	public function create(WebsocketPresenter $presenter, string $name): DataGrid {
		$grid = $this->dataGridFactory->create($presenter, $name);
		$this->configManager->setComponent('shape::WebsocketCppService');
		$grid->setDataSource($this->configManager->list());
		$grid->addColumnText('instance', 'config.websocket.form.instance');
		$grid->addColumnNumber('WebsocketPort', 'config.websocket.form.WebsocketPort');
		$grid->addAction('edit-service', 'config.actions.Edit')->setIcon('pencil')
			->setClass('btn btn-xs btn-info');
		$grid->addAction('delete-service', 'config.actions.Remove')->setIcon('remove')
			->setClass('btn btn-xs btn-danger ajax')
			->setConfirm('config.websocket.service.messages.confirmDelete', 'instance');
		$grid->addToolbarButton('add-service', 'config.actions.Add')
			->setClass('btn btn-xs btn-success');
		return $grid;
	}

}
