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

use App\ConfigModule\Datagrids\MqMessagingDataGridFactory;
use App\ConfigModule\Forms\MqFormFactory;
use App\ConfigModule\Model\GenericManager;
use Nette\Forms\Form;
use Nette\Utils\JsonException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * MQ interface configuration presenter
 */
class MqPresenter extends GenericPresenter {

	/**
	 * @var MqFormFactory MQ interface configuration form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * @var MqMessagingDataGridFactory MQ messaging configuration data grid factory
	 * @inject
	 */
	public $dataGridFactory;

	/**
	 * Constructor
	 * @param GenericManager $genericManager Generic configuration manager
	 */
	public function __construct(GenericManager $genericManager) {
		$components = ['iqrf::MqMessaging'];
		parent::__construct($components, $genericManager);
	}

	/**
	 * Edit MQ interface
	 * @param int $id ID of MQ interface
	 */
	public function renderEdit(int $id): void {
		$this->template->id = $id;
	}

	/**
	 * Delete MQ interface
	 * @param int $id ID of MQ interface
	 * @throws JsonException
	 */
	public function actionDelete(int $id): void {
		$this->configManager->setComponent('iqrf::MqMessaging');
		$this->configManager->delete($id);
		$this->redirect('Mq:default');
		$this->setView('default');
	}

	/**
	 * Create MQ messaging data grid
	 * @param string $name Data grid's component name
	 * @return DataGrid MQ messaging data grid
	 * @throws DataGridColumnStatusException
	 * @throws DataGridException
	 * @throws JsonException
	 */
	protected function createComponentConfigMqDataGrid(string $name): DataGrid {
		return $this->dataGridFactory->create($this, $name);
	}

	/**
	 * Create MQ interface configuration form
	 * @return Form MQ interface configuration form
	 * @throws JsonException
	 */
	protected function createComponentConfigMqForm(): Form {
		return $this->formFactory->create($this);
	}

}
