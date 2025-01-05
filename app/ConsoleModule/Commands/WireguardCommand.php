<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

namespace App\ConsoleModule\Commands;

use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\WireguardInterfaceRepository;
use App\NetworkModule\Models\WireguardManager;

abstract class WireguardCommand extends EntityManagerCommand {

	/**
	 * @var WireguardManager Wireguard manager
	 */
	protected WireguardManager $manager;

	/**
	 * @var WireguardInterfaceRepository Wireguard interface repository
	 */
	protected WireguardInterfaceRepository $repository;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 * @param WireguardManager $manager Wireguard manager
	 * @param string|null $name Command name
	 */
	public function __construct(EntityManager $entityManager, WireguardManager $manager, ?string $name = null) {
		parent::__construct($entityManager, $name);
		$this->manager = $manager;
		$this->repository = $entityManager->getWireguardInterfaceRepository();
	}

}
