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

use App\ConfigModule\Datagrids\ComponentsDataGridFactory;
use App\ConfigModule\Forms\ComponentsFormFactory;
use App\ConfigModule\Models\ComponentManager;
use App\CoreModule\Presenters\ProtectedPresenter;
use App\CoreModule\Traits\TPresenterFlashMessage;
use Nette\Forms\Form;
use Nette\IOException;
use Nette\Utils\JsonException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * Component configuration presenter
 */
class ComponentPresenter extends ProtectedPresenter {

	use TPresenterFlashMessage;

	/**
	 * @var ComponentsDataGridFactory Daemon's components data grid
	 * @inject
	 */
	public $dataGridFactory;

	/**
	 * @var ComponentsFormFactory Daemon's components configuration form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * @var ComponentManager Component manager
	 */
	private $configManager;

	/**
	 * Constructor
	 * @param ComponentManager $componentManager Component manager
	 */
	public function __construct(ComponentManager $componentManager) {
		$this->configManager = $componentManager;
		parent::__construct();
	}

	/**
	 * Catches exceptions
	 */
	public function actionDefault(): void {
		try {
			$this->configManager->list();
		} catch (IOException $e) {
			$this->flashError('config.messages.readFailures.ioError');
			$this->redirect('Homepage:default');
		} catch (JsonException $e) {
			$this->flashError('config.messages.readFailures.invalidJson');
			$this->redirect('Homepage:default');
		}
	}

	/**
	 * Renders a list of components
	 * @throws JsonException
	 */
	public function renderDefault(): void {
		$this->template->components = $this->configManager->list();
	}

	/**
	 * Edits the component
	 * @param int $id Component ID
	 */
	public function renderEdit(int $id): void {
		$this->template->id = $id;
	}

	/**
	 * Deletes the component
	 * @param int $id Component ID
	 * @throws JsonException
	 */
	public function actionDelete(int $id): void {
		if ($this->user->isInRole('power')) {
			try {
				$this->configManager->delete($id);
				$this->flashSuccess('config.messages.successes.delete');
			} catch (IOException $e) {
				$this->flashError('config.messages.deleteFailures.ioError');
			}
		}
		$this->redirect('Component:default');
	}

	/**
	 * Creates the components data grid
	 * @param string $name Data grid's component name
	 * @return DataGrid Components data grid
	 * @throws DataGridColumnStatusException
	 * @throws DataGridException
	 * @throws JsonException
	 */
	protected function createComponentConfigComponentsDataGrid(string $name): DataGrid {
		return $this->dataGridFactory->create($this, $name);
	}

	/**
	 * Creates the components configuration form
	 * @return Form Components configuration form
	 * @throws JsonException
	 */
	protected function createComponentConfigComponentsForm(): Form {
		return $this->formFactory->create($this);
	}

}
