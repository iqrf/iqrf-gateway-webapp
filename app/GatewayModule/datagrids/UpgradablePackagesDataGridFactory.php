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

namespace App\GatewayModule\Datagrids;

use App\CoreModule\Datagrids\DataGridFactory;
use App\GatewayModule\Exceptions\UnsupportedPackageManagerException;
use App\GatewayModule\Models\UpdaterManager;
use App\GatewayModule\Presenters\UpdaterPresenter;
use Nette\SmartObject;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * Upgradable packages data grid
 */
class UpgradablePackagesDataGridFactory {

	use SmartObject;

	/**
	 * @var DataGridFactory Data grid factory
	 */
	private $dataGridFactory;

	/**
	 * @var UpdaterManager IQRF Gateway Updater manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param DataGridFactory $dataGridFactory Generic data grid factory
	 * @param UpdaterManager $manager IQRF Gateway Updater manager
	 */
	public function __construct(DataGridFactory $dataGridFactory, UpdaterManager $manager) {
		$this->dataGridFactory = $dataGridFactory;
		$this->manager = $manager;
	}

	/**
	 * Creates the user data grid
	 * @param UpdaterPresenter $presenter IQRF Gateway Updater presenter
	 * @param string $name Data grid's component name
	 * @return DataGrid User data grid
	 * @throws DataGridColumnStatusException
	 * @throws DataGridException
	 */
	public function create(UpdaterPresenter $presenter, string $name): DataGrid {
		$grid = $this->dataGridFactory->create($presenter, $name);
		try {
			$grid->setDataSource($this->manager->getUpgradable());
		} catch (UnsupportedPackageManagerException $e) {
			$presenter->redirect('Updater:default');
		}
		$grid->addColumnText('name', 'gateway.updater.upgradablePackages.packageName');
		$grid->addColumnText('oldVersion', 'gateway.updater.upgradablePackages.oldVersion');
		$grid->addColumnText('newVersion', 'gateway.updater.upgradablePackages.newVersion');
		return $grid;
	}

}
