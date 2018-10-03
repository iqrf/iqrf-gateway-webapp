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

namespace App\ConfigModule\Datagrids;

use App\ConfigModule\Presenters\MqttPresenter;
use App\ConfigModule\Model\GenericManager;
use App\CoreModule\Exception\NonExistingJsonSchemaException;
use App\CoreModule\Datagrids\DataGridFactory;
use Nette;
use Nette\IOException;
use Ublaboo\DataGrid\DataGrid;

/**
 * Render a MQTT messaging datagrid
 */
class MqttMessagingDataGridFactory {

	use Nette\SmartObject;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $configManager;

	/**
	 * @var DataGridFactory Data grid factory
	 */
	private $datagridFactory;

	/**
	 * @var MqttPresenter MQTT interface configuration presenter
	 */
	private $presenter;

	/**
	 * Constructor
	 * @param DataGridFactory $datagridFactory Generic datagrid factory
	 * @param GenericManager $configManager Generic configuration manager
	 */
	public function __construct(DataGridFactory $datagridFactory, GenericManager $configManager) {
		$this->datagridFactory = $datagridFactory;
		$this->configManager = $configManager;
	}

	/**
	 * Create MQTT messaging datagrid
	 * @param MqttPresenter $presenter MQTT interface configuration presenter
	 * @param string $name Datagrid's component name
	 * @return DataGrid MQTT messaging datagrid
	 */
	public function create(MqttPresenter $presenter, string $name): DataGrid {
		$this->presenter = $presenter;
		$grid = $this->datagridFactory->create($presenter, $name);
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
			->setConfirm('config.mqtt.form.messages.confirmDelete', 'instance');
		$grid->addToolbarButton('add', 'config.actions.Add')
			->setClass('btn btn-xs btn-success');
		return $grid;
	}

	/**
	 * Change status of the async messaging
	 * @param int $id Component ID
	 * @param bool $status New async messaging status
	 */
	public function changeAsyncMsg(int $id, bool $status): void {
		$this->changeConfiguration($id, 'acceptAsyncMsg', $status);
	}

	/**
	 * Change status of TLS support
	 * @param int $id Component ID
	 * @param bool $status New TLS support
	 */
	public function changeTls(int $id, bool $status): void {
		$this->changeConfiguration($id, 'EnabledSSL', $status);
	}

	/**
	 * Change configuration
	 * @param int $id ID
	 * @param string $key Key to change
	 * @param mixed $value New value
	 */
	private function changeConfiguration(int $id, string $key, $value): void {
		$config = $this->configManager->load($id);
		$config[$key] = $value;
		try {
			$this->configManager->save($config);
			$this->presenter->flashMessage('config.messages.success', 'success');
		} catch (IOException $e) {
			$this->presenter->flashMessage('config.messages.writeFailures.ioError', 'danger');
		} catch (NonExistingJsonSchemaException $e) {
			$this->presenter->flashMessage('config.messages.writeFailures.nonExistingJsonSchema', 'danger');
		} finally {
			if ($this->presenter->isAjax()) {
				$this->presenter->redrawControl('flashes');
				$this->presenter['configMqttDataGrid']->setDataSource($this->configManager->list());
				$this->presenter['configMqttDataGrid']->redrawItem($id);
			}
		}
	}

}
