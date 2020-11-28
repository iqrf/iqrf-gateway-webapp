<?php

/**
 * TEST: App\ConfigModule\Models\ControllerConfigManager
 * @covers App\ConfigModule\Models\ControllerConfigManager
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Integration\ConfigModule\Models;

use App\ConfigModule\Models\ControllerConfigManager;
use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\JsonFileManager;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Controller configuration manager
 */
final class ControllerConfigManagerTest extends TestCase {

	/**
	 * Controller configuration file name
	 */
	private const FILE_NAME = 'config';

	/**
	 * Controller configuration directory path
	 */
	private const CONF_DIR = __DIR__ . '/../../../data/controller/';

	/**
	 * Controller configuration temporary directory path
	 */
	private const TEMP_CONF_DIR = __DIR__ . '/../../../temp/controller/';

	/**
	 * @var ControllerConfigManager Controller configuration manager
	 */
	private $manager;

	/**
	 * @var ControllerConfigManager Controller configuration temporary manager
	 */
	private $managerTemp;

	/**
	 * Tests the function to retrieve Controller configuration
	 */
	public function testGetConfig(): void {
		$expected = [
			'daemonApi' => [
				'autoNetwork' => [
					'actionRetries' => 1,
					'discoveryBeforeStart' => true,
					'discoveryTxPower' => 6,
					'skipDiscoveryEachWave' => false,
					'stopConditions' => [
						'abortOnTooManyNodesFound' => false,
						'emptyWaves' => 2,
						'waves' => 2,
					],
					'returnVerbose' => false,
				],
				'discovery' => [
					'maxAddr' => 0,
					'txPower' => 6,
					'returnVerbose' => false,
				],
			],
			'factoryReset' => [
				'coordinator' => false,
				'daemon' => true,
				'network' => false,
				'webapp' => true,
			],
			'logger' => [
				'filePath' => '/var/log/iqrf-gateway-controller.log',
				'severity' => 'info',
			],
			'resetButton' => [
				'api' => '',
				'button' => 2,
			],
			'statusLed' => [
				'greenLed' => 0,
				'redLed' => 1,
			],
			'wsServers' => [
				'api' => 'ws://localhost:1338',
				'monitor' => 'ws://localhost:1438',
			],
		];
		Assert::equal($expected, $this->manager->getConfig());
	}

	/**
	 * Tests the function to update Controller configuration file
	 */
	public function testSaveConfig(): void {
		Environment::lock('controller_config', __DIR__ . '/../../../temp/');
		$expected = $this->manager->getConfig();
		$expected['daemonApi']['discovery']['maxAddr'] = 5;
		$expected['factoryReset']['coordinator'] = true;
		$expected['logger']['severity'] = 'debug';
		$expected['resetButton']['api'] = 'discovery';
		$expected['statusLed']['greenLed'] = 1;
		$expected['wsServers']['api'] = 'ws://localhost:1883';
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
		$this->manager = new ControllerConfigManager($fileManager);
		$this->managerTemp = new ControllerConfigManager($fileManagerTemp);
	}

}

$test = new ControllerConfigManagerTest();
$test->run();
