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

use App\ConfigModule\Models\GenericManager;
use App\ConfigModule\Presenters\UdpPresenter;
use App\CoreModule\Datagrids\DataGridFactory;
use Nette\SmartObject;
use Nette\Utils\JsonException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * Render a UDP messaging data grid
 */
class UdpMessagingDataGridFactory {

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
	 * Create UDP messaging data grid
	 * @param UdpPresenter $presenter UDP configuration presenter
	 * @param string $name Data grid's component name
	 * @return DataGrid UDP messaging data grid
	 * @throws DataGridException
	 * @throws JsonException
	 */
	public function create(UdpPresenter $presenter, string $name): DataGrid {
		$grid = $this->dataGridFactory->create($presenter, $name);
		$this->configManager->setComponent('iqrf::UdpMessaging');
		$grid->setDataSource($this->configManager->list());
		$grid->addColumnText('instance', 'config.udp.form.instance');
		$grid->addColumnNumber('RemotePort', 'config.udp.form.RemotePort');
		$grid->addColumnNumber('LocalPort', 'config.udp.form.LocalPort');
		$grid->addAction('edit', 'config.actions.Edit')->setIcon('pencil')
			->setClass('btn btn-xs btn-info');
		$grid->addAction('delete', 'config.actions.Remove')->setIcon('remove')
			->setClass('btn btn-xs btn-danger ajax')
			->setConfirm('config.udp.form.messages.confirmDelete', 'instance');
		$grid->addToolbarButton('add', 'config.actions.Add')
			->setClass('btn btn-xs btn-success');
		return $grid;
	}

}
