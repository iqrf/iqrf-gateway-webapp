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

namespace App\NetworkModule\Presenters;

use App\NetworkModule\Datagrids\EthernetDatagridFactory;
use App\NetworkModule\Forms\EthernetFormFactory;
use Nette\Application\UI\Form;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * Ethernet network manager presenter
 */
class EthernetPresenter extends BasePresenter {

	/**
	 * @var EthernetDatagridFactory Ethernet network connection datagrid
	 * @inject
	 */
	public $dataGridFactory;

	/**
	 * @var EthernetFormFactory Ethernet network connection configuration form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * @var UuidInterface|null Network connection UUID
	 */
	private $interfaceUuid = null;

	/**
	 * Edits the network connection configuration
	 * @param string $uuid Network connection UUID
	 */
	public function renderEdit(string $uuid): void {
		try {
			$this->interfaceUuid = Uuid::fromString($uuid);
		} catch (InvalidUuidStringException $e) {
			$this->flashError('network.ethernet.messages.invalidUuid');
			$this->redirect('Ethernet:default');
		}
	}

	/**
	 * Creates the Ethernet network connection data grid
	 * @param string $name Component name
	 * @return DataGrid Ethernet network connection data grid
	 * @throws DataGridException
	 */
	protected function createComponentEthernetDataGrid(string $name): DataGrid {
		return $this->dataGridFactory->create($this, $name);
	}

	/**
	 * Creates the Ethernet network configuration form
	 * @return Form Ethernet network configuration form
	 */
	protected function createComponentEthernetForm(): Form {
		return $this->formFactory->create($this, $this->interfaceUuid);
	}

}
