<?php

/**
 * TEST: App\Models\WebSocket\ProxyConfigManager
 * @covers App\Models\WebSocket\ProxyConfigManager
 * @phpVersion >= 8.2
 * @testCase
 */
/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
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

namespace Tests\Unit\Models\WebSocket;

use App\Models\WebSocket\ProxyConfigManager;
use Iqrf\CommandExecutor\CommandExecutor;
use Iqrf\CommandExecutor\CommandStack;
use Iqrf\FileManager\FileManager;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for proxy configuration manager
 */
final class ProxyConfigManagerTest extends TestCase {

	/**
	 * Data dir path
	 */
	private const CONF_DIR = TESTER_DIR . '/data/proxy/';

	/**
	 * @var ProxyConfigManager Proxy configuration manager
	 */
	private ProxyConfigManager $manager;

	/**
	 * @var ProxyConfigManager Proxy configuration manager
	 */
	private ProxyConfigManager $managerMissing;

	/**
	 * Tests the function read configuration (from a valid file)
	 */
	public function testReadConfig(): void {
		Assert::equal(
			[
				'host' => 'localhost',
				'port' => 9005,
				'address' => '127.0.0.1',
				'upstream' => 'ws://iqube.local/ws',
				'token' => 'iqrfgd2;1;ETi3v8RGLVGXb/uNenhskEiSH/2KussEbantcvjfGQ4=',
			],
			$this->manager->readConfig(),
		);
	}

	/**
	 * Tests the function to read config (from missing file)
	 */
	public function testReadConfigMissing(): void {
		Assert::equal(
			[
				'host' => ProxyConfigManager::DEFAULT_HOST,
				'port' => ProxyConfigManager::DEFAULT_PORT,
				'address' => ProxyConfigManager::DEFAULT_ADDRESS,
				'upstream' => ProxyConfigManager::DEFAULT_UPSTREAM,
				'token' => '',
			],
			$this->managerMissing->readConfig(),
		);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$commandManager = new CommandExecutor(false, new CommandStack());
		$this->manager = new ProxyConfigManager(
			new FileManager(
				self::CONF_DIR,
				$commandManager,
			),
		);
		$this->managerMissing = new ProxyConfigManager(
			new FileManager(
				self::CONF_DIR . 'missing/',
				$commandManager,
			),
		);
	}

}

$test = new ProxyConfigManagerTest();
$test->run();
