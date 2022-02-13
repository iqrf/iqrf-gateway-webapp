<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

use App\GatewayModule\Models\Backup\RestoreLogger;
use Mockery;
use Mockery\MockInterface;
use Tester\TestCase;

/**
 * Backup and restore test case
 */
abstract class BackupTestCase extends TestCase {

	/**
	 * Path to zip file containing backup data
	 */
	protected const ZIP_PATH = TESTER_DIR . '/data/iqrf-gateway-backup.zip';

	/**
	 * Path to temporary zip file containing backup data
	 */
	protected const TEMP_ZIP_PATH = TMP_DIR . '/iqrf-gateway-backup.zip';

	/**
	 * @var RestoreLogger|MockInterface Restore logger mock
	 */
	protected $logger;

	/**
	 * Sets up test environment
	 */
	protected function setUp(): void {
		$this->logger = Mockery::mock(RestoreLogger::class);
	}

	/**
	 * Test environment cleanup
	 */
	protected function tearDown(): void {
		Mockery::close();
	}

}
