<?php

/**
 * TEST: App\ConfigModule\Models\TranslatorConfigManager
 * @covers App\ConfigModule\Models\TranslatorConfigManager
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Integration\ConfigModule\Models;

use App\ConfigModule\Models\TranslatorConfigManager;
use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\JsonFileManager;
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
	 * Translator configuration file name
	 */
	private const FILE_NAME = 'config';

	/**
	 * Translator configuration directory path
	 */
	private const CONF_DIR = __DIR__ . '/../../../data/translator/';

	/**
	 * Translator configuration temporary directory path
	 */
	private const TEMP_CONF_DIR = __DIR__ . '/../../../temp/translator/';

	/**
	 * @var TranslatorConfigManager Translator configuration manager
	 */
	private $manager;

	/**
	 * @var TranslatorConfigManager Translator configuration temporary manager
	 */
	private $managerTemp;

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
		Environment::lock('translator_config', __DIR__ . '/../../../temp/');
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
		$fileManager = new JsonFileManager(self::CONF_DIR, $commandManager);
		$fileManagerTemp = new JsonFileManager(self::TEMP_CONF_DIR, $commandManager);
		$this->manager = new TranslatorConfigManager($fileManager);
		$this->managerTemp = new TranslatorConfigManager($fileManagerTemp);
	}

}

$test = new TranslatorConfigManagerTest();
$test->run();
