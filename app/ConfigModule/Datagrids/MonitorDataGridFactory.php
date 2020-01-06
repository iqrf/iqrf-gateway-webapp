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

use App\ConfigModule\Models\MonitorManager;
use App\ConfigModule\Presenters\MonitorPresenter;
use App\CoreModule\Datagrids\DataGridFactory;
use App\CoreModule\Exceptions\NonExistingJsonSchemaException;
use Nette\IOException;
use Nette\SmartObject;
use Nette\Utils\JsonException;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * Daemon's monitor service data grid factory
 */
class MonitorDataGridFactory {

	use SmartObject;

	/**
	 * @var DataGridFactory Data grid factory
	 */
	private $factory;

	/**
	 * @var MonitorPresenter Daemon's monitor service configuration presenter
	 */
	private $presenter;

	/**
	 * @var MonitorManager Daemon's monitor service configuration manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param DataGridFactory $factory Data grid factory
	 * @param MonitorManager $manager Daemon's monitor service configuration manager
	 */
	public function __construct(DataGridFactory $factory, MonitorManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Creates Daemon's monitor service data grid
	 * @param MonitorPresenter $presenter Daemon's monitor service configuration presenter
	 * @param string $name Data grid's component name
	 * @return DataGrid Daemon's monitor service data grid
	 * @throws DataGridColumnStatusException
	 * @throws DataGridException
	 * @throws JsonException
	 */
	public function create(MonitorPresenter $presenter, string $name): DataGrid {
		$this->presenter = $presenter;
		$grid = $this->factory->create($presenter, $name);
		$grid->setDataSource($this->manager->list());
		$grid->addColumnNumber('reportPeriod', 'config.monitor.form.reportPeriod');
		$grid->addColumnNumber('port', 'config.monitor.form.WebsocketPort');
		$grid->addColumnStatus('acceptOnlyLocalhost', 'config.websocket.form.acceptOnlyLocalhost')
			->addOption(true, 'config.components.form.enabled')->setIcon('ok')->endOption()
			->addOption(false, 'config.components.form.disabled')
			->setIcon('remove')->setClass('btn btn-xs btn-danger')->endOption()
			->onChange[] = [$this, 'changeOnlyLocalhost'];
		$grid->addAction('edit', 'config.actions.Edit')->setIcon('pencil')
			->setClass('btn btn-xs btn-info');
		$grid->addAction('delete', 'config.actions.Remove')->setIcon('remove')
			->setClass('btn btn-xs btn-danger ajax')
			->setConfirmation(new StringConfirmation('config.monitor.messages.confirmDelete', 'monitorInstance'));
		$grid->addToolbarButton('add', 'config.actions.Add')
			->setClass('btn btn-xs btn-success');
		return $grid;
	}

	/**
	 * Changes the Daemon's monitor service configuration
	 * @param string $id Daemon's monitor service configuration ID
	 * @param string $key Key to change
	 * @param mixed $value New value
	 * @throws JsonException
	 */
	private function changeConfiguration(string $id, string $key, $value): void {
		$config = $this->manager->load((int) $id);
		$config[$key] = $value;
		try {
			$this->manager->save($config);
			$this->presenter->flashSuccess('config.messages.success');
		} catch (IOException $e) {
			$this->presenter->flashError('config.messages.writeFailures.ioError');
		} catch (NonExistingJsonSchemaException $e) {
			$this->presenter->flashError('config.messages.writeFailures.nonExistingJsonSchema');
		} finally {
			if ($this->presenter->isAjax()) {
				$this->presenter->redrawControl('flashes');
				$dataGrid = $this->presenter['configMonitorDataGrid'];
				$dataGrid->setDataSource($this->manager->list());
				$dataGrid->redrawItem($id);
			}
		}
	}

	/**
	 * Changes the status of accepting connections only from localhost
	 * @param string $id Component ID
	 * @param string $status New accepting connections only from localhost status
	 * @throws JsonException
	 */
	public function changeOnlyLocalhost(string $id, string $status): void {
		$this->changeConfiguration($id, 'acceptOnlyLocalhost', boolval($status));
	}

}
