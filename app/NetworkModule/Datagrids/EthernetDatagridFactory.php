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

namespace App\NetworkModule\Datagrids;

use App\CoreModule\Datagrids\DataGridFactory;
use App\NetworkModule\Enums\ConnectionTypes;
use App\NetworkModule\Enums\InterfaceTypes;
use App\NetworkModule\Models\ConnectionManager;
use App\NetworkModule\Models\InterfaceManager;
use App\NetworkModule\Presenters\EthernetPresenter;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * Ethernet network connection datagrid
 */
final class EthernetDatagridFactory {

	/**
	 * Translator prefix for the datagrid
	 */
	private const PREFIX = 'network.ethernet.datagrid.';

	/**
	 * @var ConnectionManager Network connection manager
	 */
	private $connectionManager;

	/**
	 * @var DataGridFactory Data grid factory
	 */
	private $factory;

	/**
	 * @var InterfaceManager Network interface manager
	 */
	private $interfaceManager;

	/**
	 * Ethernet network connection datagrid constructor
	 * @param DataGridFactory $factory Data grid factory
	 * @param ConnectionManager $connectionManager Network connection manager
	 * @param InterfaceManager $interfaceManager Network interface manager
	 */
	public function __construct(DataGridFactory $factory, ConnectionManager $connectionManager, InterfaceManager $interfaceManager) {
		$this->connectionManager = $connectionManager;
		$this->factory = $factory;
		$this->interfaceManager = $interfaceManager;
	}

	/**
	 * Creates Ethernet network connection data grid
	 * @param EthernetPresenter $presenter Ethernet network connection presenter
	 * @param string $name Data grid's component name
	 * @return DataGrid Ethernet network connection data grid
	 * @throws DataGridException
	 */
	public function create(EthernetPresenter $presenter, string $name): DataGrid {
		$grid = $this->factory->create($presenter, $name);
		$grid->setPrimaryKey('uuid');
		$grid->setDataSource($this->list());
		$grid->addColumnText('name', self::PREFIX . 'name');
		$grid->addColumnText('state', self::PREFIX . 'state');
		$grid->addAction('edit', self::PREFIX . 'edit')
			->setIcon('pencil')
			->setClass('btn btn-xs btn-info')
			->setRenderCondition(function (array $item): bool {
				return $item['state'] === 'connected';
			});
		return $grid;
	}

	/**
	 * Lists Ethernet network connections
	 * @return array<string, array<string, string>> Ethernet network connections
	 */
	private function list(): array {
		$array = [];
		$interfaces = $this->interfaceManager->list(InterfaceTypes::ETHERNET());
		$connections = $this->connectionManager->list(ConnectionTypes::ETHERNET());
		foreach ($interfaces as $interface) {
			foreach ($connections as $connection) {
				if ($connection->getInterfaceName() === $interface->getName()) {
					$array[$interface->getName()] = $interface->jsonSerialize();
					$array[$interface->getName()]['uuid'] = $connection->getUuid()->toString();
				}
			}
		}
		return $array;
	}

}
