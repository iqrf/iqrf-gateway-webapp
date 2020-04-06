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

use App\ConfigModule\Datagrids\TraceFileDataGridFactory;
use App\ConfigModule\Forms\TraceFileFormFactory;
use App\ConfigModule\Models\GenericManager;
use Nette\Application\UI\Form;
use Nette\IOException;
use Nette\Utils\JsonException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * Tracer configuration presenter
 */
class TracerPresenter extends GenericPresenter {

	/**
	 * @var TraceFileFormFactory Daemon's tracer configuration form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * @var TraceFileDataGridFactory Tracer configuration data grid factory
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
	 * Edits the tracer configuration
	 * @param int $id ID of UDP interface
	 */
	public function renderEdit(int $id): void {
		$this->template->id = $id;
	}

	/**
	 * Deletes the tracer service
	 * @param int $id ID of tracer service
	 * @throws JsonException
	 */
	public function actionDelete(int $id): void {
		$this->configManager->setComponent('shape::TraceFileService');
		try {
			$fileName = $this->configManager->getFileNameById($id);
			$this->configManager->deleteFile($fileName);
			$this->flashSuccess('config.messages.successes.delete');
		} catch (IOException $e) {
			$this->flashError('config.messages.deleteFailures.ioError');
		}
		$this->redirect('TracerFile:default');
	}

	/**
	 * Creates the Tracer form
	 * @return Form Tracer form
	 */
	protected function createComponentConfigTracerForm(): Form {
		return $this->formFactory->create($this);
	}

	/**
	 * Creates tracer data grid
	 * @param string $name Data grid's component name
	 * @return DataGrid Tracer data grid
	 * @throws DataGridException
	 * @throws JsonException
	 */
	protected function createComponentConfigTracerDataGrid(string $name): DataGrid {
		return $this->dataGridFactory->create($this, $name);
	}

}
