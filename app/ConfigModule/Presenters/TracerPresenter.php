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
use Nette\Application\UI\Form;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * Tracer configuration presenter
 */
class TracerPresenter extends GenericPresenter {

	/**
	 * IQRF Gateway Daemon component name
	 */
	private const COMPONENT = 'shape::TraceFileService';

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
	 * Edits the tracer configuration
	 * @param int $id ID of UDP interface
	 */
	public function actionEdit(int $id): void {
		$this->loadFormConfiguration($this['configTracerForm'], self::COMPONENT, $id, 'Tracer:default', [$this, 'configurationLoad']);
	}

	/**
	 * Loads the instance configuration
	 * @param int $id Instance ID
	 * @return array<string, mixed> Instance configuration
	 * @throws JsonException
	 */
	public function configurationLoad(int $id): array {
		$defaults = $this->manager->load($id);
		foreach ($defaults['VerbosityLevels'] as &$verbosityLevel) {
			$level = Strings::upper($verbosityLevel['level']);
			$verbosityLevels = ['ERR', 'WAR', 'INF', 'DBG'];
			if (in_array($level, $verbosityLevels, true)) {
				$verbosityLevel['level'] = $level;
			} else {
				unset($verbosityLevel['level']);
			}
		}
		return $defaults;
	}

	/**
	 * Deletes the tracer service
	 * @param int $id ID of tracer service
	 */
	public function actionDelete(int $id): void {
		$this->deleteInstance(self::COMPONENT, $id, 'Tracer:default');
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
