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

use App\ConfigModule\Datagrids\MqttMessagingDataGridFactory;
use App\ConfigModule\Forms\MqttFormFactory;
use Nette\Application\UI\Form;
use Nette\Utils\JsonException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * MQTT interface configuration presenter
 */
class MqttPresenter extends GenericPresenter {

	/**
	 * IQRF Gateway Daemon component name
	 */
	private const COMPONENT = 'iqrf::MqttMessaging';

	/**
	 * @var MqttFormFactory MQTT interface configuration form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * @var MqttMessagingDataGridFactory MQTT interface data grid factory
	 * @inject
	 */
	public $dataGridFactory;

	/**
	 * Edits the MQTT interface
	 * @param int $id ID of MQTT interface
	 */
	public function actionEdit(int $id): void {
		$this->loadFormConfiguration($this['configMqttForm'], self::COMPONENT, $id, 'Mqtt::default');
	}

	/**
	 * Deletes the MQTT interface
	 * @param int $id ID of MQTT interface
	 */
	public function actionDelete(int $id): void {
		$this->deleteInstance(self::COMPONENT, $id, 'Mqtt:default');
	}

	/**
	 * Creates the MQTT interface data grid
	 * @param string $name Data grid's component name
	 * @return DataGrid MQTT interface data grid
	 * @throws DataGridColumnStatusException
	 * @throws DataGridException
	 * @throws JsonException
	 */
	protected function createComponentConfigMqttDataGrid(string $name): DataGrid {
		return $this->dataGridFactory->create($this, $name);
	}

	/**
	 * Creates the MQTT interface configuration form
	 * @return Form MQTT interface configuration form
	 */
	protected function createComponentConfigMqttForm(): Form {
		return $this->formFactory->create($this);
	}

}
