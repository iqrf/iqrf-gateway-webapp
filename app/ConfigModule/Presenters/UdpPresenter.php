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

use App\ConfigModule\Datagrids\UdpMessagingDataGridFactory;
use App\ConfigModule\Forms\UdpFormFactory;
use Nette\Application\UI\Form;
use Nette\Utils\JsonException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * UDP interface configuration presenter
 */
class UdpPresenter extends GenericPresenter {

	/**
	 * IQRF Gateway Daemon component name
	 */
	private const COMPONENT = 'iqrf::UdpMessaging';

	/**
	 * @var UdpFormFactory UDP interface configuration form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * @var UdpMessagingDataGridFactory UDP messaging configuration data grid factory
	 * @inject
	 */
	public $dataGridFactory;

	/**
	 * Edits the UDP interface
	 * @param int $id ID of UDP interface
	 */
	public function actionEdit(int $id): void {
		$this->loadFormConfiguration($this['configUdpForm'], self::COMPONENT, $id, 'Udp:default');
	}

	/**
	 * Deletes the UDP interface
	 * @param int $id ID of UDP interface
	 */
	public function actionDelete(int $id): void {
		$this->deleteInstance(self::COMPONENT, $id, 'Udp:default');
	}

	/**
	 * Creates the UDP messaging data grid
	 * @param string $name Data grid's component name
	 * @return DataGrid UDP messaging data grid
	 * @throws DataGridException
	 * @throws JsonException
	 */
	protected function createComponentConfigUdpDataGrid(string $name): DataGrid {
		return $this->dataGridFactory->create($this, $name);
	}

	/**
	 * Creates the UDP interface configuration form
	 * @return Form UDP interface configuration form
	 */
	protected function createComponentConfigUdpForm(): Form {
		return $this->formFactory->create($this);
	}

}
