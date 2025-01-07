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
	 * @var WireguardInterfaceRepository WireGuard interface repository
	 */
	protected readonly WireguardInterfaceRepository $repository;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 * @param WireguardManager $manager WireGuard manager
	 */
	public function __construct(
		EntityManager $entityManager,
		protected readonly WireguardManager $manager,
	) {
		parent::__construct($entityManager);
		$this->repository = $entityManager->getWireguardInterfaceRepository();
	}

}
