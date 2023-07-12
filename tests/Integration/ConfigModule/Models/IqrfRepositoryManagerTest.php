<?php

/**
 * TEST: App\ConfigModule\Models\IqrfRepositoryManager
 * @covers App\ConfigModule\Models\IqrfRepositoryManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

namespace Tests\Integration\ConfigModule\Models;

use App\ConfigModule\Models\IqrfRepositoryManager;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for IQRF Repository configuration manager
 */
final class IqrfRepositoryManagerTest extends TestCase {

	/**
	 * IQRF Repository configuration directory path
	 */
	private const CONF_PATH = TESTER_DIR . '/data/iqrf-repository.neon';

	/**
	 * IQRF Repository configuration temporary directory path
	 */
	private const TEMP_CONF_PATH = TMP_DIR . '/iqrf-repository.neon';

	/**
	 * Tests the function to read IQRF Repository configuration (nonexistent file)
	 */
	public function testReadNonexitent(): void {
		$manager = new IqrfRepositoryManager(TMP_DIR . '/nonexistent.neon');
		$expected = [
			'apiEndpoint' => 'https://repository.iqrfalliance.org/api',
			'credentials' => [
				'username' => null,
				'password' => null,
			],
		];
		Assert::same($expected, $manager->readConfig());
	}

	/**
	 * Tests the function to read IQRF Repository configuration
	 */
	public function testRead(): void {
		$manager = new IqrfRepositoryManager(self::CONF_PATH);
		$expected = [
			'apiEndpoint' => 'https://devrepo.iqrfalliance.org/api',
			'credentials' => [
				'username' => null,
				'password' => null,
			],
		];
		Assert::same($expected, $manager->readConfig());
	}

	/**
	 * Tests the function to write IQRF Repository configuration
	 */
	public function testWrite(): void {
		FileSystem::copy(self::CONF_PATH, self::TEMP_CONF_PATH);
		$manager = new IqrfRepositoryManager(self::TEMP_CONF_PATH);
		$config = [
			'apiEndpoint' => 'https://devrepo.iqrfalliance.org/api',
			'credentials' => [
				'username' => 'iqrf',
				'password' => 'iqrf',
			],
		];
		$manager->saveConfig($config);
		Assert::same($config, $manager->readConfig());
	}

}

$test = new IqrfRepositoryManagerTest();
$test->run();
