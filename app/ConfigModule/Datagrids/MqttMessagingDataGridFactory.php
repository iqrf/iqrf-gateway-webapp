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

namespace App\ConfigModule\Datagrids;

use App\ConfigModule\Models\GenericManager;
use App\ConfigModule\Presenters\MqttPresenter;
use App\CoreModule\Datagrids\DataGridFactory;
use App\CoreModule\Exceptions\NonExistingJsonSchemaException;
use Nette\IOException;
use Nette\SmartObject;
use Nette\Utils\JsonException;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * MQTT messaging data grid
 */
class MqttMessagingDataGridFactory {

	use SmartObject;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $configManager;

	/**
	 * @var DataGridFactory Data grid factory
	 */
	private $dataGridFactory;

	/**
	 * @var MqttPresenter MQTT interface configuration presenter
	 */
	private $presenter;

	/**
	 * Constructor
	 * @param DataGridFactory $dataGridFactory Generic data grid factory
	 * @param GenericManager $configManager Generic configuration manager
	 */
	public function __construct(DataGridFactory $dataGridFactory, GenericManager $configManager) {
		$this->dataGridFactory = $dataGridFactory;
		$this->configManager = $configManager;
	}

	/**
	 * Creates the MQTT messaging data grid
	 * @param MqttPresenter $presenter MQTT interface configuration presenter
	 * @param string $name Data grid's component name
	 * @return DataGrid MQTT messaging data grid
	 * @throws DataGridColumnStatusException
	 * @throws DataGridException
	 * @throws JsonException
	 */
	public function create(MqttPresenter $presenter, string $name): DataGrid {
		$this->presenter = $presenter;
		$grid = $this->dataGridFactory->create($presenter, $name);
		$this->configManager->setComponent('iqrf::MqttMessaging');
		$grid->setDataSource($this->configManager->list());
		$grid->addColumnText('instance', 'config.mqtt.form.instance');
		$grid->addColumnText('BrokerAddr', 'config.mqtt.form.BrokerAddr');
		$grid->addColumnText('ClientId', 'config.mqtt.form.ClientId');
		$grid->addColumnText('TopicRequest', 'config.mqtt.form.TopicRequest');
		$grid->addColumnText('TopicResponse', 'config.mqtt.form.TopicResponse');
		$grid->addColumnStatus('EnabledSSL', 'config.mqtt.form.EnabledSSL')
			->addOption(true, 'config.components.form.enabled')->setIcon('ok')->endOption()
			->addOption(false, 'config.components.form.disabled')
			->setIcon('remove')->setClass('btn btn-xs btn-danger')->endOption()
			->onChange[] = [$this, 'changeTls'];
		$grid->addColumnStatus('acceptAsyncMsg', 'config.mqtt.asyncMessages')
			->addOption(true, 'config.components.form.enabled')->setIcon('ok')->endOption()
			->addOption(false, 'config.components.form.disabled')
			->setIcon('remove')->setClass('btn btn-xs btn-danger')->endOption()
			->onChange[] = [$this, 'changeAsyncMsg'];
		$grid->addAction('edit', 'config.actions.Edit')->setIcon('pencil')
			->setClass('btn btn-xs btn-info');
		$grid->addAction('delete', 'config.actions.Remove')->setIcon('remove')
			->setClass('btn btn-xs btn-danger ajax')
			->setConfirmation(new StringConfirmation('config.mqtt.form.messages.confirmDelete', 'instance'));
		$grid->addToolbarButton('add', 'config.actions.Add')
			->setClass('btn btn-xs btn-success');
		return $grid;
	}

	/**
	 * Changes the status of the asynchronous messaging
	 * @param string $id Component ID
	 * @param string $status New asynchronous messaging status
	 * @throws JsonException
	 */
	public function changeAsyncMsg(string $id, string $status): void {
		$this->changeConfiguration($id, 'acceptAsyncMsg', boolval($status));
	}

	/**
	 * Changes the MQTT messaging configuration
	 * @param string $id MQTT messaging configuration ID
	 * @param string $key Key to change
	 * @param mixed $value New value
	 * @throws JsonException
	 */
	private function changeConfiguration(string $id, string $key, $value): void {
		$id = intval($id);
		$config = $this->configManager->load($id);
		$config[$key] = $value;
		try {
			$this->configManager->save($config);
			$this->presenter->flashSuccess('config.messages.success');
		} catch (IOException $e) {
			$this->presenter->flashError('config.messages.writeFailures.ioError');
		} catch (NonExistingJsonSchemaException $e) {
			$this->presenter->flashError('config.messages.writeFailures.nonExistingJsonSchema');
		} finally {
			if ($this->presenter->isAjax()) {
				$this->presenter->redrawControl('flashes');
				$dataGrid = $this->presenter['configMqttDataGrid'];
				$dataGrid->setDataSource($this->configManager->list());
				$dataGrid->redrawItem($id);
			}
		}
	}

	/**
	 * Changes the status of TLS support
	 * @param string $id Component ID
	 * @param string $status New TLS support
	 * @throws JsonException
	 */
	public function changeTls(string $id, string $status): void {
		$this->changeConfiguration($id, 'EnabledSSL', boolval($status));
	}

}
