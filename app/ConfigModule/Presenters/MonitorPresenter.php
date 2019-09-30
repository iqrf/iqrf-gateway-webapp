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

namespace App\ConfigModule\Presenters;

use App\ConfigModule\Datagrids\MonitorDataGridFactory;
use App\ConfigModule\Forms\MonitorFormFactory;
use App\ConfigModule\Models\GenericManager;
use App\ConfigModule\Models\MonitorManager;
use Nette\Application\UI\Form;
use Nette\IOException;
use Nette\Utils\JsonException;
use Ublaboo\DataGrid\DataGrid;

/**
 * Daemon's monitor service configuration presenter
 */
class MonitorPresenter extends GenericPresenter {

	/**
	 * @var MonitorDataGridFactory Daemon's monitor service data grid factory
	 * @inject
	 */
	public $dataGridFactory;

	/**
	 * @var MonitorFormFactory Daemon's monitor service configuration form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * @var array<string,string> Components
	 */
	protected $components = [
		'monitor' => 'iqrf::MonitorService',
		'webSocket' => 'shape::WebsocketCppService',
	];

	/**
	 * @var MonitorManager Daemon's monitor service configuration manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param GenericManager $genericManager Generic configuration manager
	 * @param MonitorManager $monitorManager Daemon's monitor service configuration manager
	 */
	public function __construct(GenericManager $genericManager, MonitorManager $monitorManager) {
		$this->manager = $monitorManager;
		parent::__construct($this->components, $genericManager);
	}

	/**
	 * Deletes the Daemon's monitor service
	 * @param int $id Daemon's monitor service ID
	 * @throws JsonException
	 */
	public function actionDelete(int $id): void {
		try {
			$this->manager->delete($id);
			$this->flashSuccess('config.messages.successes.delete');
		} catch (IOException $e) {
			$this->flashError('config.messages.deleteFailures.ioError');
		}
		$this->redirect('Monitor:default');
	}

	/**
	 * Edits the Daemon's monitor service
	 * @param int $id Daemon's monitor service ID
	 */
	public function renderEdit(int $id): void {
		$this->template->id = $id;
	}

	/**
	 * Creates the daemon's monitor service data grid
	 * @param string $name Data grid's component name
	 * @return DataGrid Daemon's monitor service data grid
	 */
	protected function createComponentConfigMonitorDataGrid(string $name): DataGrid {
		return $this->dataGridFactory->create($this, $name);
	}

	/**
	 * Creates the daemon's monitor service configuration form
	 * @return Form Daemon's monitor service configuration form
	 */
	protected function createComponentConfigMonitorForm(): Form {
		return $this->formFactory->create($this);
	}

}
