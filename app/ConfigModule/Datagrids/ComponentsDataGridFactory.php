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
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use Nette\IOException;
use Nette\Utils\JsonException;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * Daemon's components data grid
 */
class ComponentsDataGridFactory {

	/**
	 * Prefix for translator
	 */
	private const TRANSLATE_PREFIX = 'config.components.form.';

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
		$grid->setDataSource([]);
		$grid->addColumnText('name', self::TRANSLATE_PREFIX . 'name')
			->setSortable();
		if ($this->presenter->getUser()->isInRole('power')) {
			$grid->addColumnNumber('startlevel', self::TRANSLATE_PREFIX . 'startlevel')
				->setSortable();
		}
		$grid->addColumnText('libraryPath', self::TRANSLATE_PREFIX . 'libraryPath')
			->setSortable();
		$grid->addColumnText('libraryName', self::TRANSLATE_PREFIX . 'libraryName')
			->setSortable();
		$grid->addColumnStatus('enabled', self::TRANSLATE_PREFIX . 'enabled')
			->addOption(true, self::TRANSLATE_PREFIX . 'enabled')
			->setIcon('ok')
			->endOption()
			->addOption(false, self::TRANSLATE_PREFIX . 'disabled')
			->setIcon('remove')
			->setClass('btn btn-xs btn-danger')
			->endOption()
			->setSortable()
			->onChange[] = [$this, 'changeStatus'];
		$grid->addAction('edit', 'config.actions.Edit')
			->setIcon('pencil')
			->setClass('btn btn-xs btn-info');
		$grid->addAction('delete', 'config.actions.Remove')
			->setIcon('remove')
			->setClass('btn btn-xs btn-danger ajax')
			->setConfirmation(new StringConfirmation(self::TRANSLATE_PREFIX . 'messages.confirmDelete', 'name'));
		$grid->allowRowsAction('delete', function (): bool {
			return $this->presenter->getUser()->isInRole('power');
		});
		$grid->addToolbarButton('add', 'config.actions.Add')
			->setIcon('plus')
			->setClass('btn btn-xs btn-success');
		return $grid;
	}

	/**
	 * Loads the data to the data grid
	 * @return array<int, array<string, bool|int|string>> Data for the data grid
	 */
	public function load(): array {
		$components = $this->configManager->list();
		if ($this->presenter->getUser()->isInRole('power')) {
			return $components;
		}
		$whitelisted = ['iqrf::IqrfCdc', 'iqrf::IqrfSpi', 'iqrf::IqrfUart'];
		$filtered = [];
		foreach ($components as $component) {
			if (in_array($component['name'], $whitelisted, true)) {
				$filtered[] = $component;
			}
		}
		return $filtered;
	}

	/**
	 * Changes the status of the component
	 * @param string $id Component ID
	 * @param string $status New component's status
	 * @throws JsonException
	 */
	public function changeStatus(string $id, string $status): void {
		$config = $this->configManager->load((int) $id);
		$config['enabled'] = boolval($status);
		try {
			$this->configManager->save($config, (int) $id);
			$this->presenter->flashSuccess('config.messages.success');
		} catch (IOException $e) {
			$this->presenter->flashError('config.messages.writeFailures.ioError');
		} catch (NonexistentJsonSchemaException $e) {
			$this->presenter->flashError('config.messages.writeFailures.nonExistingJsonSchema');
		} finally {
			if ($this->presenter->isAjax()) {
				$dataGrid = $this->presenter['configComponentsDataGrid'];
				try {
					$dataSource = $this->load();
				} catch (IOException | JsonException $e) {
					$dataSource = [];
				}
				$dataGrid->setDataSource($dataSource);
				$dataGrid->reloadTheWholeGrid();
			}
		}
	}

}
