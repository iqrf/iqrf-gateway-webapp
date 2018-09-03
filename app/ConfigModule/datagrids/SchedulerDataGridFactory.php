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

use App\ConfigModule\Presenters\SchedulerPresenter;
use App\ConfigModule\Model\SchedulerManager;
use App\CoreModule\Datagrids\DataGridFactory;
use Nette;
use Ublaboo\DataGrid\DataGrid;

/**
 * Render a components datagrid
 */
class SchedulerDataGridFactory {

	use Nette\SmartObject;

	/**
	 * @var SchedulerManager Scheduler's tasks manager
	 */
	private $configManager;

	/**
	 * @var DataGridFactory Data grid factory
	 */
	private $datagridFactory;

	/**
	 * Constructor
	 * @param DataGridFactory $datagridFactory Generic datagrid factory
	 * @param SchedulerManager $configManager Scheduler's tasks manager
	 */
	public function __construct(DataGridFactory $datagridFactory, SchedulerManager $configManager) {
		$this->datagridFactory = $datagridFactory;
		$this->configManager = $configManager;
	}

	/**
	 * Create component datagrid
	 * @param SchedulerPresenter $presenter Scheduler's tasks configuration presenter
	 * @param string $name Datagrid's component name
	 * @return DataGrid Scheduler's tasks datagrid
	 */
	public function create(SchedulerPresenter $presenter, string $name): DataGrid {
		$grid = $this->datagridFactory->create($presenter, $name);
		$grid->setDataSource($this->configManager->list());
		$grid->addColumnNumber('id', 'config.scheduler.form.id');
		$grid->addColumnText('time', 'config.scheduler.form.time');
		$grid->addColumnText('service', 'config.scheduler.form.service');
		$grid->addColumnText('type', 'config.scheduler.form.type');
		$grid->addColumnText('request', 'config.scheduler.form.request');
		$grid->addAction('edit', 'config.actions.Edit')->setIcon('pencil')
				->setClass('btn btn-xs btn-info');
		$grid->addAction('delete', 'config.actions.Remove')->setIcon('remove')
				->setClass('btn btn-xs btn-danger ajax')
				->setConfirm('config.scheduler.form.messages.confirmDelete', 'id');
		return $grid;
	}

}
