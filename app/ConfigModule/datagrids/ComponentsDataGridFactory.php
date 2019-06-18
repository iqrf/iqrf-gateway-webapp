<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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

use App\ConfigModule\Models\ComponentManager;
use App\ConfigModule\Presenters\ComponentPresenter;
use App\CoreModule\Datagrids\DataGridFactory;
use App\CoreModule\Exceptions\NonExistingJsonSchemaException;
use Nette\IOException;
use Nette\SmartObject;
use Nette\Utils\JsonException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * Daemon's components data grid
 */
class ComponentsDataGridFactory {

	use SmartObject;

	/**
	 * @var ComponentManager Component manager
	 */
	private $configManager;

	/**
	 * @var DataGridFactory Data grid factory
	 */
	private $dataGridFactory;

	/**
	 * @var ComponentPresenter Component's configuration presenter
	 */
	private $presenter;

	/**
	 * Constructor
	 * @param DataGridFactory $dataGridFactory Generic data grid factory
	 * @param ComponentManager $componentManager Component manager
	 */
	public function __construct(DataGridFactory $dataGridFactory, ComponentManager $componentManager) {
		$this->dataGridFactory = $dataGridFactory;
		$this->configManager = $componentManager;
	}

	/**
	 * Creates the component data grid
	 * @param ComponentPresenter $presenter Component configuration presenter
	 * @param string $name Data grid's component name
	 * @return DataGrid Component data grid
	 * @throws DataGridColumnStatusException
	 * @throws DataGridException
	 * @throws JsonException
	 */
	public function create(ComponentPresenter $presenter, string $name): DataGrid {
		$this->presenter = $presenter;
		$grid = $this->dataGridFactory->create($presenter, $name);
		$grid->setDataSource($this->load());
		$grid->addColumnText('name', 'config.components.form.name');
		$grid->addColumnText('libraryPath', 'config.components.form.libraryPath');
		$grid->addColumnText('libraryName', 'config.components.form.libraryName');
		$grid->addColumnStatus('enabled', 'config.components.form.enabled')
			->addOption(true, 'config.components.form.enabled')->setIcon('ok')->endOption()
			->addOption(false, 'config.components.form.disabled')->setIcon('remove')
			->setClass('btn btn-xs btn-danger')->endOption()
			->onChange[] = [$this, 'changeStatus'];
		$grid->addAction('edit', 'config.actions.Edit')->setIcon('pencil')
			->setClass('btn btn-xs btn-info');
		$grid->addAction('delete', 'config.actions.Remove')->setIcon('remove')
			->setClass('btn btn-xs btn-danger ajax')
			->setConfirm('config.components.form.messages.confirmDelete', 'name');
		$grid->allowRowsAction('delete', function () {
			return $this->presenter->user->isInRole('power');
		});
		$grid->addToolbarButton('add', 'config.actions.Add')
			->setClass('btn btn-xs btn-success');
		return $grid;
	}

	/**
	 * Loads the data to the data grid
	 * @return mixed[] Data for the data grid
	 * @throws JsonException
	 */
	private function load(): array {
		if ($this->presenter->user->isInRole('power')) {
			return $this->configManager->list();
		}
		$visible = ['iqrf::IqrfCdc', 'iqrf::IqrfSpi', 'iqrf::IqrfUart'];
		$components = [];
		foreach ($this->configManager->list() as $component) {
			if (in_array($component['name'], $visible, true)) {
				$components[] = $component;
			}
		}
		return $components;
	}

	/**
	 * Changes the status of the component
	 * @param int $id Component ID
	 * @param bool $status New component's status
	 * @throws JsonException
	 */
	public function changeStatus(int $id, bool $status): void {
		$config = $this->configManager->load($id);
		$config['enabled'] = $status;
		try {
			$this->configManager->save($config, $id);
			$this->presenter->flashSuccess('config.messages.success');
		} catch (IOException $e) {
			$this->presenter->flashError('config.messages.writeFailures.ioError');
		} catch (NonExistingJsonSchemaException $e) {
			$this->presenter->flashError('config.messages.writeFailures.nonExistingJsonSchema');
		} finally {
			if ($this->presenter->isAjax()) {
				$this->presenter->redrawControl('flashes');
				$dataGrid = $this->presenter['configComponentsDataGrid'];
				$dataGrid->setDataSource($this->load());
				$dataGrid->redrawItem($id);
			}
		}
	}

}
