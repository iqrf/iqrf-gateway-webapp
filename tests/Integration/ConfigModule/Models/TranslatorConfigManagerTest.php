<?php

/**
 * TEST: App\ConfigModule\Models\TranslatorConfigManager
 * @covers App\ConfigModule\Models\TranslatorConfigManager
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

use App\ConfigModule\Models\TranslatorConfigManager;
use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FileManager;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Translator configuration manager
 */
final class TranslatorConfigManagerTest extends TestCase {

	/**
	 * @var string Translator configuration directory path
	 */
	private const CONF_DIR = TESTER_DIR . '/data/translator/';

	/**
	 * @var string Translator configuration temporary directory path
	 */
	private const TEMP_CONF_DIR = TMP_DIR . '/translator/';

	/**
	 * @var TranslatorConfigManager Translator configuration manager
	 */
	private TranslatorConfigManager $manager;

	/**
	 * @var TranslatorConfigManager Translator configuration temporary manager
	 */
	private TranslatorConfigManager $managerTemp;

	/**
	 * Tests the function to read Translator configuration file
	 */
	public function testGetConfig(): void {
		$expected = [
			'rest' => [
				'api_key' => '',
				'addr' => 'localhost',
				'port' => 80,
			],
			'mqtt' => [
				'cid' => 'testClientId',
				'addr' => 'localhost',
				'port' => 1883,
				'topic' => 'testTopic',
				'user' => '',
				'pw' => '',
			],
		];
		Assert::equal($expected, $this->manager->getConfig());
	}

	/**
	 * Tests the function to update Controller configuration file
	 */
	public function testSaveConfig(): void {
		Environment::lock('translator_config', TMP_DIR);
		$expected = $this->manager->getConfig();
		$expected['rest']['port'] = 20;
		$expected['mqtt']['user'] = 'testUser';
		$this->managerTemp->saveConfig($expected);
		Assert::same($expected, $this->managerTemp->getConfig());
	}

	/**
	 * Sets up the test enviornment
	 */
	protected function setUp(): void {
		FileSystem::copy(self::CONF_DIR, self::TEMP_CONF_DIR);
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$fileManager = new FileManager(self::CONF_DIR, $commandManager);
		$fileManagerTemp = new FileManager(self::TEMP_CONF_DIR, $commandManager);
		$this->manager = new TranslatorConfigManager($fileManager);
		$this->managerTemp = new TranslatorConfigManager($fileManagerTemp);
	}

}

$test = new TranslatorConfigManagerTest();
$test->run();
