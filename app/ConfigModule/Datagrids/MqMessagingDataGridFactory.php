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
use App\ConfigModule\Presenters\MqPresenter;
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
 * MQ messaging data grid
 */
class MqMessagingDataGridFactory {

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
	 * @var MqPresenter MQ interface configuration presenter
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
	 * Creates the MQ messaging data grid
	 * @param MqPresenter $presenter MQ interface configuration presenter
	 * @param string $name Data grid's component name
	 * @return DataGrid MQ messaging data grid
	 * @throws DataGridColumnStatusException
	 * @throws DataGridException
	 * @throws JsonException
	 */
	public function create(MqPresenter $presenter, string $name): DataGrid {
		$this->presenter = $presenter;
		$grid = $this->dataGridFactory->create($presenter, $name);
		$this->configManager->setComponent('iqrf::MqMessaging');
		$grid->setDataSource($this->configManager->list());
		$grid->addColumnText('instance', 'config.mq.form.instance');
		$grid->addColumnText('LocalMqName', 'config.mq.form.LocalMqName');
		$grid->addColumnText('RemoteMqName', 'config.mq.form.RemoteMqName');
		$grid->addColumnStatus('acceptAsyncMsg', 'config.mq.form.acceptAsyncMsg')
			->addOption(true, 'config.components.form.enabled')
			->setIcon('ok')
			->endOption()
			->addOption(false, 'config.components.form.disabled')
			->setIcon('remove')
			->setClass('btn btn-xs btn-danger')
			->endOption()
			->onChange[] = [$this, 'changeAsyncMsg'];
		$grid->addAction('edit', 'config.actions.Edit')
			->setIcon('pencil')
			->setClass('btn btn-xs btn-info');
		$grid->addAction('delete', 'config.actions.Remove')
			->setIcon('remove')
			->setClass('btn btn-xs btn-danger ajax')
			->setConfirmation(new StringConfirmation('config.mq.form.messages.confirmDelete', 'instance'));
		$grid->addToolbarButton('add', 'config.actions.Add')
			->setClass('btn btn-xs btn-success')
			->setIcon('plus');
		return $grid;
	}

	/**
	 * Changes status of the asynchronous messaging
	 * @param string $id Component ID
	 * @param string $status New asynchronous messaging status
	 * @throws JsonException
	 */
	public function changeAsyncMsg(string $id, string $status): void {
		$config = $this->configManager->load((int) $id);
		$config['acceptAsyncMsg'] = boolval($status);
		try {
			$this->configManager->save($config);
			$this->presenter->flashSuccess('config.messages.success');
		} catch (IOException $e) {
			$this->presenter->flashError('config.messages.writeFailures.ioError');
		} catch (NonExistingJsonSchemaException $e) {
			$this->presenter->flashError('config.messages.writeFailures.nonExistingJsonSchema');
		} finally {
			if ($this->presenter->isAjax()) {
				$dataGrid = $this->presenter['configMqDataGrid'];
				$dataGrid->setDataSource($this->configManager->list());
				$dataGrid->reloadTheWholeGrid();
			}
		}
	}

}
