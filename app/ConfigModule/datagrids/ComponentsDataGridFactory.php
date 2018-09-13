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

use App\ConfigModule\Presenters\ComponentPresenter;
use App\ConfigModule\Model\ComponentManager;
use App\CoreModule\Datagrids\DataGridFactory;
use Nette;
use Ublaboo\DataGrid\DataGrid;

/**
 * Render a components datagrid
 */
class ComponentsDataGridFactory {

	use Nette\SmartObject;

	/**
	 * @var ComponentManager Component manager
	 */
	private $configManager;

	/**
	 * @var DataGridFactory Data grid factory
	 */
	private $datagridFactory;

	/**
	 * @var ComponentPresenter Component's configuration presenter
	 */
	private $presenter;

	/**
	 * Constructor
	 * @param DataGridFactory $datagridFactory Generic datagrid factory
	 * @param ComponentManager $componentManager Component manager
	 */
	public function __construct(DataGridFactory $datagridFactory, ComponentManager $componentManager) {
		$this->datagridFactory = $datagridFactory;
		$this->configManager = $componentManager;
	}

	/**
	 * Create component datagrid
	 * @param ComponentPresenter $presenter Component configuration presenter
	 * @param string $name Datagrid's component name
	 * @return DataGrid Component datagrid
	 */
	public function create(ComponentPresenter $presenter, string $name): DataGrid {
		$this->presenter = $presenter;
		$grid = $this->datagridFactory->create($presenter, $name);
		$grid->setDataSource($this->load());
		$grid->addColumnText('name', 'config.components.form.name');
		$grid->addColumnText('libraryPath', 'config.components.form.libraryPath');
		$grid->addColumnText('libraryName', 'config.components.form.libraryName');
		$grid->addColumnStatus('enabled', 'config.components.form.enabled')
				->addOption(true, 'config.components.form.enabled')->setIcon('ok')->endOption()
				->addOption(false, 'config.components.form.disabled')
				->setIcon('remove')->setClass('btn btn-xs btn-danger')->endOption();
		$grid->addAction('edit', 'config.actions.Edit')->setIcon('pencil')
				->setClass('btn btn-xs btn-info');
		$grid->addAction('delete', 'config.actions.Remove')->setIcon('remove')
				->setClass('btn btn-xs btn-danger ajax')
				->setConfirm('config.components.form.messages.confirmDelete', 'name');
		$grid->allowRowsAction('delete', function() {
			return $this->presenter->user->isInRole('power');
		});
		$grid->addToolbarButton('add', 'config.actions.Add')
				->setClass('btn btn-xs btn-success');
		return $grid;
	}

	/**
	 * Load data to the datagrid
	 * @return array Data for the datagrid
	 */
	private function load(): array {
		if ($this->presenter->user->isInRole('power')) {
			return $this->configManager->list();
		} else {
			$visible = ['iqrf::IqrfCdc', 'iqrf::IqrfSpi', 'iqrf::IqrfUart', 'iqrf::Scheduler',];
			$components = [];
			foreach ($this->configManager->list() as $component) {
				if (in_array($component['name'], $visible, true)) {
					$components[] = $component;
				}
			}
			return $components;
		}
	}

}
