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
declare(strict_types=1);

namespace App\ConfigModule\Presenters;

use App\ConfigModule\Datagrids\TraceFileDataGridFactory;
use App\ConfigModule\Forms\TraceFileFormFactory;
use App\ConfigModule\Models\GenericManager;
use Nette\Forms\Form;
use Nette\Utils\JsonException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

class TracerPresenter extends GenericPresenter {

	/**
	 * @var TraceFileFormFactory Daemon's tracer configuration form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * @var TraceFileDataGridFactory Trace files configuration data grid factory
	 * @inject
	 */
	public $dataGridFactory;

	/**
	 * Constructor
	 * @param GenericManager $genericManager Generic configuration manager
	 */
	public function __construct(GenericManager $genericManager) {
		$components = ['shape::TraceFileService'];
		parent::__construct($components, $genericManager);
	}

	/**
	 * Edit UDP interface
	 * @param int $id ID of UDP interface
	 */
	public function renderEdit(int $id): void {
		$this->template->id = $id;
	}

	/**
	 * Delete trace file service
	 * @param int $id ID of trace file service
	 * @throws JsonException
	 */
	public function actionDelete(int $id): void {
		$this->configManager->setComponent('shape::TraceFileService');
		$this->configManager->delete($id);
		$this->redirect('TracerFile:default');
		$this->setView('default');
	}

	/**
	 * Create Tracer form
	 * @return Form Tracer form
	 */
	protected function createComponentConfigTracerForm(): Form {
		return $this->formFactory->create($this);
	}

	/**
	 * Create Trace file data grid
	 * @param string $name Data grid's component name
	 * @return DataGrid Trace file data grid
	 * @throws DataGridException
	 * @throws JsonException
	 */
	protected function createComponentConfigTracerDataGrid(string $name): DataGrid {
		return $this->dataGridFactory->create($this, $name);
	}

}
