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

namespace App\GatewayModule\Presenters;

use App\CoreModule\Presenters\ProtectedPresenter;
use App\CoreModule\Traits\TPresenterFlashMessage;
use App\GatewayModule\Datagrids\UpgradablePackagesDataGridFactory;
use App\GatewayModule\Exceptions\UnsupportedPackageManagerException;
use App\GatewayModule\Models\UpdaterManager;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * IQRF Gateway updater presenter
 */
class UpdaterPresenter extends ProtectedPresenter {

	use TPresenterFlashMessage;

	/**
	 * @var UpgradablePackagesDataGridFactory Upgradable packages data grid factory
	 * @inject
	 */
	public $dataGridFactory;

	/**
	 * @var UpdaterManager Updater manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param UpdaterManager $manager Updater manager
	 */
	public function __construct(UpdaterManager $manager) {
		$this->manager = $manager;
		parent::__construct();
	}

	/**
	 * Creates the data grid with upgradable packages
	 * @param string $name Component name
	 * @return DataGrid Datagrid with upgradable packages
	 * @throws DataGridColumnStatusException
	 * @throws DataGridException
	 */
	protected function createComponentUpgradablePackagesGrid(string $name): DataGrid {
		return $this->dataGridFactory->create($this, $name);
	}

	/**
	 * Handles listing of upgradable packages
	 */
	public function handleList(): void {
		try {
			$this->manager->listUpgradable([$this, 'showCommandOutput']);
			$this->template->upgradablePackages = true;
			$this->redrawControl('upgradablePackages');
		} catch (UnsupportedPackageManagerException $e) {
			$this->flashError('gateway.updater.messages.unsupportedManager');
			$this->redrawControl('flashes');
		}
	}

	/**
	 * Handles updating package's cache
	 */
	public function handleUpdate(): void {
		try {
			$this->manager->update(function (string $type, ?string $buffer): void {
				$this->showCommandOutput($type, $buffer);
				$this->redrawControl('outputChange');
			});
		} catch (UnsupportedPackageManagerException $e) {
			$this->flashError('gateway.updater.messages.unsupportedManager');
			$this->redrawControl('flashes');
		}
	}

	/**
	 * Handles updating packages
	 */
	public function handleUpgrade(): void {
		try {
			$this->manager->upgrade([$this, 'showCommandOutput']);
		} catch (UnsupportedPackageManagerException $e) {
			$this->flashError('gateway.updater.messages.unsupportedManager');
			$this->redrawControl('flashes');
		}
	}

	/**
	 * Shows shell command output
	 * @param string $type Output type
	 * @param string|null $buffer Output buffer
	 */
	public function showCommandOutput(string $type, ?string $buffer): void {
		if (isset($this->template->outputBuffer)) {
			$this->template->outputBuffer .= $buffer;
		} else {
			$this->template->outputBuffer = $buffer;
		}
		$this->redrawControl('outputChange');
	}

}
