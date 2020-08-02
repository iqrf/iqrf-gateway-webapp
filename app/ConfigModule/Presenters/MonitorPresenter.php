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
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use Nette\Application\UI\Form;
use Nette\InvalidArgumentException;
use Nette\IOException;
use Nette\Utils\JsonException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * Daemon's monitor service configuration presenter
 */
class MonitorPresenter extends GenericPresenter {

	/**
	 * Redirect
	 */
	private const REDIRECT = 'Monitor:default';

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
	 * @var MonitorManager Daemon's monitor service configuration manager
	 */
	private $monitorManager;

	/**
	 * Constructor
	 * @param GenericManager $genericManager Generic configuration manager
	 * @param MonitorManager $monitorManager Daemon's monitor service configuration manager
	 */
	public function __construct(GenericManager $genericManager, MonitorManager $monitorManager) {
		$this->monitorManager = $monitorManager;
		parent::__construct($genericManager);
	}

	/**
	 * Deletes the Daemon's monitor service
	 * @param int $id Daemon's monitor service ID
	 * @throws JsonException
	 */
	public function actionDelete(int $id): void {
		try {
			$this->monitorManager->delete($id);
			$this->flashSuccess('config.messages.successes.delete');
		} catch (IOException $e) {
			$this->flashError('config.messages.deleteFailures.ioError');
		}
		$this->redirect(self::REDIRECT);
	}

	/**
	 * Edits the Daemon's monitor service
	 * @param int $id Daemon's monitor service ID
	 */
	public function actionEdit(int $id): void {
		try {
			$configuration = $this->monitorManager->load($id);
			if ($configuration === []) {
				$this->flashError('config.messages.readFailures.notFound');
				$this->redirect(self::REDIRECT);
			}
			$this['configMonitorForm']->setDefaults($configuration);
		} catch (NonexistentJsonSchemaException $e) {
			$this->flashError('config.messages.readFailures.nonExistingJsonSchema');
			$this->redirect(self::REDIRECT);
		} catch (IOException $e) {
			$this->flashError('config.messages.readFailures.ioError');
			$this->redirect(self::REDIRECT);
		} catch (JsonException $e) {
			$this->flashError('config.messages.readFailures.invalidJson');
			$this->redirect(self::REDIRECT);
		} catch (InvalidArgumentException $e) {
			$this->flashError('config.messages.readFailures.notFound');
			$this->redirect(self::REDIRECT);
		}
	}

	/**
	 * Creates the daemon's monitor service data grid
	 * @param string $name Data grid's component name
	 * @return DataGrid Daemon's monitor service data grid
	 * @throws DataGridColumnStatusException
	 * @throws DataGridException
	 * @throws JsonException
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
