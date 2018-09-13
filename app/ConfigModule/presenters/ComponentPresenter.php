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

namespace App\ConfigModule\Presenters;

use App\ConfigModule\Datagrids\ComponentsDataGridFactory;
use App\ConfigModule\Forms\ComponentsFormFactory;
use App\ConfigModule\Model\ComponentManager;
use App\CoreModule\Presenters\ProtectedPresenter;
use Nette\Forms\Form;
use Nette\IOException;
use Nette\Utils\JsonException;
use Ublaboo\DataGrid\DataGrid;

/**
 * Component configuration presenter
 */
class ComponentPresenter extends ProtectedPresenter {

	/**
	 * @var ComponentManager Component manager
	 */
	private $configManager;

	/**
	 * @var ComponentsDataGridFactory Daemon's components datagrid
	 * @inject
	 */
	public $datagridFactory;

	/**
	 * @var ComponentsFormFactory Daemon's components configuration form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * Constructor
	 * @param ComponentManager $componentManager Component manager
	 */
	public function __construct(ComponentManager $componentManager) {
		$this->configManager = $componentManager;
		parent::__construct();
	}

	/**
	 * Catch exceptions
	 */
	public function actionDefault(): void {
		try {
			$this->configManager->list();
		} catch (IOException $e) {
			$this->flashMessage('config.messages.readFailure', 'danger');
			$this->redirect('Homepage:default');
		} catch (JsonException $e) {
			$this->flashMessage('config.messages.invalidJson', 'danger');
			$this->redirect('Homepage:default');
		}
	}

	/**
	 * Render list of components
	 */
	public function renderDefault(): void {
		$this->template->components = $this->configManager->list();
	}

	/**
	 * Edit component
	 * @param int $id Component ID
	 */
	public function renderEdit(int $id): void {
		$this->template->id = $id;
	}

	/**
	 * Delete component
	 * @param int $id Component ID
	 */
	public function actionDelete(int $id): void {
		if ($this->user->isInRole('power')) {
			$this->configManager->delete($id);
		}
		$this->redirect('Component:default');
		$this->setView('default');
	}

	/**
	 * Create components datagrid
	 * @param string $name Datagrid's component name
	 * @return DataGrid Components datagrid
	 */
	protected function createComponentConfigComponentsDataGrid(string $name): DataGrid {
		return $this->datagridFactory->create($this, $name);
	}

	/**
	 * Create components form
	 * @return Form Components form
	 */
	protected function createComponentConfigComponentsForm(): Form {
		return $this->formFactory->create($this);
	}

}
