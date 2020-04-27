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

use App\ConfigModule\Datagrids\MqMessagingDataGridFactory;
use App\ConfigModule\Forms\MqFormFactory;
use App\ConfigModule\Models\GenericManager;
use Nette\Application\UI\Form;
use Nette\IOException;
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
	 * @var MqMessagingDataGridFactory MQ interface data grid factory
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
	 * Edits the MQ interface
	 * @param int $id ID of MQ interface
	 */
	public function renderEdit(int $id): void {
		$this->template->id = $id;
	}

	/**
	 * Deletes the MQ interface
	 * @param int $id ID of MQ interface
	 * @throws JsonException
	 */
	public function actionDelete(int $id): void {
		$this->configManager->setComponent('iqrf::MqMessaging');
		try {
			$fileName = $this->configManager->getFileNameById($id);
			$this->configManager->deleteFile($fileName);
			$this->flashSuccess('config.messages.successes.delete');
		} catch (IOException $e) {
			$this->flashError('config.messages.deleteFailures.ioError');
		}
		$this->redirect('Mq:default');
	}

	/**
	 * Creates the MQ interface data grid
	 * @param string $name Data grid's component name
	 * @return DataGrid MQ interface data grid
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
