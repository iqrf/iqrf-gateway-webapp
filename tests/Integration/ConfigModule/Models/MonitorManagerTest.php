<?php

/**
 * TEST: App\ConfigModule\Models\MonitorManager
 * @covers App\ConfigModule\Models\MonitorManager
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Integration\ConfigModule\Models;

use App\ConfigModule\Models\GenericManager;
use App\ConfigModule\Models\MonitorManager;
use Tester\Assert;
use Tester\Environment;
use Tests\Toolkit\TestCases\JsonConfigTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests fot Daemon's monitor service manager
 */
final class MonitorManagerTest extends JsonConfigTestCase {

	/**
	 * Service file names
	 */
	private const FILE_NAMES = [
		'monitor' => 'iqrf__MonitorService',
		'webSocket' => 'shape__WebsocketCppService_Monitor',
	];

	/**
	 * Service instances
	 */
	private const INSTANCES = [
		'monitor' => 'iqrf::MonitorService',
		'webSocket' => 'WebsocketCppService_Monitor',
	];

	/**
	 * Values from the configuration form
	 */
	private const VALUES = [
		'reportPeriod' => 15,
		'port' => 1339,
		'acceptOnlyLocalhost' => true,
	];

	/**
	 * @var MonitorManager Daemon's monitor service manager
	 */
	private $manager;

	/**
	 * @var MonitorManager Daemon's monitor service manager
	 */
	private $managerTemp;

	/**
	 * Copies a configuration
	 */
	private function copyConfiguration(): void {
		foreach (self::FILE_NAMES as $fileName) {
			$json = $this->fileManager->read($fileName);
			$this->fileManagerTemp->write($fileName, $json);
		}
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$genericManager = new GenericManager($this->fileManager, $this->schemaManager);
		$genericManagerTest = new GenericManager($this->fileManagerTemp, $this->schemaManager);
		$this->manager = new MonitorManager($genericManager, $this->schemaManager);
		$this->managerTemp = new MonitorManager($genericManagerTest, $this->schemaManager);
	}

	/**
	 * Tests the function to delete Daemon's monitor service configuration
	 */
	public function testDelete(): void {
		Environment::lock('config_monitor', __DIR__ . '/../../../temp/');
		$this->copyConfiguration();
		Assert::true($this->fileManagerTemp->exists(self::FILE_NAMES['monitor']));
		Assert::true($this->fileManagerTemp->exists(self::FILE_NAMES['webSocket']));
		$this->managerTemp->delete(0);
		Assert::false($this->fileManagerTemp->exists(self::FILE_NAMES['monitor']));
		Assert::false($this->fileManagerTemp->exists(self::FILE_NAMES['webSocket']));
	}

	/**
	 * Tests the function to list Daemon's monitor service interfaces
	 */
	public function testList(): void {
		$expected = [
			[
				'id' => 0,
				'monitorInstance' => 'iqrf::MonitorService',
				'reportPeriod' => 10,
				'webSocketInstance' => 'WebsocketCppService_Monitor',
				'port' => 1438,
				'acceptOnlyLocalhost' => false,
			],
		];
		Assert::equal($expected, $this->manager->list());
	}

	/**
	 * Tests the function to load IQRF Gatweway Daemon's monitor service configuration
	 */
	public function testLoad(): void {
		$expected = [
			'monitorInstance' => self::INSTANCES['monitor'],
			'reportPeriod' => 10,
			'webSocketInstance' => self::INSTANCES['webSocket'],
			'port' => 1438,
			'acceptOnlyLocalhost' => false,
		];
		Assert::same($expected, $this->manager->load(0));
	}

	public function testSave(): void {
		Environment::lock('config_monitor', __DIR__ . '/../../../temp/');
		$this->copyConfiguration();
		$this->managerTemp->load(0);
		$this->managerTemp->save(self::VALUES);
		$expectedWebSocket = [
			'component' => 'shape::WebsocketCppService',
			'instance' => 'WebsocketCppService_Monitor',
			'WebsocketPort' => 1339,
			'acceptOnlyLocalhost' => true,
		];
		Assert::same($expectedWebSocket, $this->fileManagerTemp->read(self::FILE_NAMES['webSocket']));
		$expectedMonitor = [
			'component' => 'iqrf::MonitorService',
			'instance' => 'iqrf::MonitorService',
			'reportPeriod' => 15,
			'RequiredInterfaces' => [
				[
					'name' => 'shape::IWebsocketService',
					'target' => ['instance' => 'WebsocketCppService_Monitor'],
				],
			],
		];
		Assert::same($expectedMonitor, $this->fileManagerTemp->read(self::FILE_NAMES['monitor']));
	}

}

$test = new MonitorManagerTest();
$test->run();
