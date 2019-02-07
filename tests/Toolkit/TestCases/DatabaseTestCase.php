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

namespace Tests\Toolkit\TestCases;

use Nette\Caching\Storages\MemoryStorage;
use Nette\Database\Connection;
use Nette\Database\Context;
use Nette\Database\Structure;
use Tester\TestCase;

/**
 * Database test case
 */
abstract class DatabaseTestCase extends TestCase {

	/**
	 * @var Context Nette Database context
	 */
	protected $context;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$connection = new Connection('sqlite::memory:');
		$cacheStorage = new MemoryStorage();
		$structure = new Structure($connection, $cacheStorage);
		$this->context = new Context($connection, $structure);
	}

}
