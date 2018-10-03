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

use App\ConfigModule\Presenters\MqPresenter;
use App\ConfigModule\Model\GenericManager;
use App\CoreModule\Exception\NonExistingJsonSchemaException;
use App\CoreModule\Datagrids\DataGridFactory;
use Nette;
use Nette\IOException;
use Ublaboo\DataGrid\DataGrid;

/**
 * Render a MQ messaging datagrid
 */
class MqMessagingDataGridFactory {

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
	 * @var MqPresenter MQ interface configuration presenter
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
	 * Create MQ messaging datagrid
	 * @param MqPresenter $presenter MQ interface configuration presenter
	 * @param string $name Datagrid's component name
	 * @return DataGrid MQ messaging datagrid
	 */
	public function create(MqPresenter $presenter, string $name): DataGrid {
		$this->presenter = $presenter;
		$grid = $this->datagridFactory->create($presenter, $name);
		$this->configManager->setComponent('iqrf::MqMessaging');
		$grid->setDataSource($this->configManager->list());
		$grid->addColumnText('instance', 'config.mq.form.instance');
		$grid->addColumnText('LocalMqName', 'config.mq.form.LocalMqName');
		$grid->addColumnText('RemoteMqName', 'config.mq.form.RemoteMqName');
		$grid->addColumnStatus('acceptAsyncMsg', 'config.mq.form.acceptAsyncMsg')
			->addOption(true, 'config.components.form.enabled')->setIcon('ok')->endOption()
			->addOption(false, 'config.components.form.disabled')
			->setIcon('remove')->setClass('btn btn-xs btn-danger')->endOption()
			->onChange[] = [$this, 'changeAsyncMsg'];
		$grid->addAction('edit', 'config.actions.Edit')->setIcon('pencil')
			->setClass('btn btn-xs btn-info');
		$grid->addAction('delete', 'config.actions.Remove')->setIcon('remove')
			->setClass('btn btn-xs btn-danger ajax')
			->setConfirm('config.mq.form.messages.confirmDelete', 'instance');
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
		$config = $this->configManager->load($id);
		$config['acceptAsyncMsg'] = $status;
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
				$this->presenter['configMqDataGrid']->setDataSource($this->configManager->list());
				$this->presenter['configMqDataGrid']->redrawItem($id);
			}
		}
	}

}
