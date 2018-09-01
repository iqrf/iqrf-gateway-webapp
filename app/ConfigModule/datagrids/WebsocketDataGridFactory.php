<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
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

use App\ConfigModule\Presenters\WebsocketPresenter;
use App\ConfigModule\Model\WebsocketManager;
use App\CoreModule\Datagrids\DataGridFactory;
use Nette;
use Ublaboo\DataGrid\DataGrid;

/**
 * Render a websocket interface datagrid
 */
class WebsocketDataGridFactory {

	use Nette\SmartObject;

	/**
	 * @var WebsocketManager Websocket manager
	 */
	private $configManager;

	/**
	 * @var DataGridFactory Data grid factory
	 */
	private $datagridFactory;

	/**
	 * Constructor
	 * @param DataGridFactory $datagridFactory Generic datagrid factory
	 * @param WebsocketManager $configManager Websocket manager
	 */
	public function __construct(DataGridFactory $datagridFactory, WebsocketManager $configManager) {
		$this->datagridFactory = $datagridFactory;
		$this->configManager = $configManager;
	}

	/**
	 * Create component datagrid
	 * @param WebsocketPresenter $presenter Websocket configuration presenter
	 * @param string $name Datagrid's component name
	 * @return DataGrid Websocket datagrid
	 */
	public function create(WebsocketPresenter $presenter, string $name): DataGrid {
		$grid = $this->datagridFactory->create($presenter, $name);
		$grid->setDataSource($this->configManager->list());
		$grid->addColumnText('messagingInstance', 'config.websocket.form.instance');
		$grid->addColumnNumber('port', 'config.websocket.form.WebsocketPort');
		$grid->addColumnStatus('acceptAsyncMsg', 'config.websocket.form.acceptAsyncMsg')
				->addOption(true, 'config.components.form.enabled')->setIcon('ok')->endOption()
				->addOption(false, 'config.components.form.disabled')
				->setIcon('remove')->setClass('btn btn-xs btn-danger')->endOption();
		$grid->addAction('edit', 'config.actions.Edit')->setIcon('pencil')
				->setClass('btn btn-xs btn-info');
		$grid->addAction('delete', 'config.actions.Remove')->setIcon('remove')
				->setClass('btn btn-xs btn-danger ajax')
				->setConfirm('config.websocket.interface.messages.confirmDelete', 'messagingInstance');
		$grid->addToolbarButton('add', 'config.actions.Add')
				->setClass('btn btn-xs btn-success');
		return $grid;
	}

}
