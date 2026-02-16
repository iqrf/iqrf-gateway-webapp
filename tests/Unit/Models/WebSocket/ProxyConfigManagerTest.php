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

use App\Entities\ProxyConfiguration;
use App\Models\WebSocket\ProxyConfigManager;
use Iqrf\CommandExecutor\CommandExecutor;
use Iqrf\CommandExecutor\CommandStack;
use Iqrf\FileManager\FileManager;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\Environment;
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
	 * Temporary data dir path
	 */
	private const TEMP_CONF_DIR = TMP_DIR . '/proxy/';

	/**
	 * @var ProxyConfigManager Proxy configuration manager
	 */
	private ProxyConfigManager $manager;

	/**
	 * @var ProxyConfigManager Proxy configuration manager with invalid file
	 */
	private ProxyConfigManager $managerInvalid;

	/**
	 * @var ProxyConfigManager Proxy configuration manager for temporary files
	 */
	private ProxyConfigManager $managerTmp;

	/**
	 * @var ProxyConfiguration Proxy configuration object
	 */
	private ProxyConfiguration $config;

	/**
	 * Tests the function read configuration file
	 */
	public function testReadConfig(): void {
		Assert::equal(
			expected: $this->config,
			actual: $this->manager->readConfig(),
		);
	}

	/**
	 * Tests the function to read configuration from invalid file or missing properties
	 */
	public function testReadConfigMissing(): void {
		Assert::equal(
			expected: new ProxyConfiguration(),
			actual: $this->managerInvalid->readConfig(),
		);
	}

	/**
	 * Tests the function to write configuration to file
	 */
	public function testWriteConfig(): void {
		$config = $this->config;
		$config->port = 9000;
		$this->managerTmp->writeConfig($config);
		Assert::equal(
			expected: $config,
			actual: $this->managerTmp->readConfig(),
		);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		Environment::lock('proxy_config', TMP_DIR);
		FileSystem::copy(self::CONF_DIR, self::TEMP_CONF_DIR);
		$commandManager = new CommandExecutor(false, new CommandStack());
		$this->manager = new ProxyConfigManager(
			new FileManager(
				self::CONF_DIR,
				$commandManager,
			),
		);
		$this->managerInvalid = new ProxyConfigManager(
			new FileManager(
				self::CONF_DIR . 'missing/',
				$commandManager,
			),
		);
		$this->managerTmp = new ProxyConfigManager(
			new FileManager(
				self::TEMP_CONF_DIR,
				$commandManager,
			),
		);
		$this->config = new ProxyConfiguration(
			host: 'localhost',
			port: 9005,
			address: '127.0.0.1',
			upstream: 'ws://iqube.local/ws',
			token: 'iqrfgd2;1;ETi3v8RGLVGXb/uNenhskEiSH/2KussEbantcvjfGQ4='
		);
	}

}

$test = new ProxyConfigManagerTest();
$test->run();
