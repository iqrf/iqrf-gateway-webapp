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

use App\ConfigModule\Models\SchedulerManager;
use App\ConfigModule\Presenters\SchedulerPresenter;
use App\CoreModule\Datagrids\DataGridFactory;
use Nette\SmartObject;
use Nette\Utils\JsonException;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * Scheduler's tasks data grid
 */
class SchedulerDataGridFactory {

	use SmartObject;

	/**
	 * @var SchedulerManager Scheduler's tasks manager
	 */
	private $configManager;

	/**
	 * @var DataGridFactory Data grid factory
	 */
	private $dataGridFactory;

	/**
	 * Constructor
	 * @param DataGridFactory $dataGridFactory Generic data grid factory
	 * @param SchedulerManager $configManager Scheduler's tasks manager
	 */
	public function __construct(DataGridFactory $dataGridFactory, SchedulerManager $configManager) {
		$this->dataGridFactory = $dataGridFactory;
		$this->configManager = $configManager;
	}

	/**
	 * Create scheduler's tasks data grid
	 * @param SchedulerPresenter $presenter Scheduler's tasks configuration presenter
	 * @param string $name Data grid's component name
	 * @return DataGrid Scheduler's tasks data grid
	 * @throws DataGridException
	 * @throws JsonException
	 */
	public function create(SchedulerPresenter $presenter, string $name): DataGrid {
		$grid = $this->dataGridFactory->create($presenter, $name);
		$grid->setDataSource($this->configManager->list());
		$grid->addColumnNumber('id', 'config.scheduler.form.id');
		$grid->addColumnText('time', 'config.scheduler.form.time');
		$grid->addColumnText('service', 'config.scheduler.form.service');
		$grid->addColumnText('mType', 'config.scheduler.form.mType');
		$grid->addAction('edit', 'config.actions.Edit')->setIcon('pencil')
			->setClass('btn btn-xs btn-info');
		$grid->addAction('delete', 'config.actions.Remove')->setIcon('remove')
			->setClass('btn btn-xs btn-danger ajax')
			->setConfirmation(new StringConfirmation('config.scheduler.form.messages.confirmDelete', 'id'));
		$grid->addToolbarButton('add', 'config.actions.Add')
			->setIcon('plus')
			->setClass('btn btn-xs btn-success');
		$grid->addToolbarButton('import', 'config.actions.Import')
			->setIcon('arrow-up')
			->setClass('btn btn-xs btn-primary');
		return $grid;
	}

}
