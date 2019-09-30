<?php

/**
 * TEST: App\ConfigModule\Models\MonitorManager
 * @covers App\ConfigModule\Models\MonitorManager
 * @phpVersion >= 7.1
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
class MonitorManagerTest extends JsonConfigTestCase {

	/**
	 * @var array<string,string> Service file names
	 */
	private $fileNames = [
		'monitor' => 'iqrf__MonitorService',
		'webSocket' => 'shape__WebsocketCppService_Monitor',
	];

	/**
	 * @var array<string,string> Service instances
	 */
	private $instances = [
		'monitor' => 'iqrf::MonitorService',
		'webSocket' => 'WebsocketCppService_Monitor',
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
	 * @var array<string,mixed> Values from the configuration form
	 */
	private $values = [
		'reportPeriod' => 15,
		'port' => 1339,
		'acceptOnlyLocalhost' => true,
	];

	/**
	 * Copies a configuration
	 */
	private function copyConfiguration(): void {
		foreach ($this->fileNames as $fileName) {
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
		Assert::true($this->fileManagerTemp->exists($this->fileNames['monitor']));
		Assert::true($this->fileManagerTemp->exists($this->fileNames['webSocket']));
		$this->managerTemp->delete(0);
		Assert::false($this->fileManagerTemp->exists($this->fileNames['monitor']));
		Assert::false($this->fileManagerTemp->exists($this->fileNames['webSocket']));
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
			'monitorInstance' => $this->instances['monitor'],
			'reportPeriod' => 10,
			'webSocketInstance' => $this->instances['webSocket'],
			'port' => 1438,
			'acceptOnlyLocalhost' => false,
		];
		Assert::same($expected, $this->manager->load(0));
	}

	public function testSave(): void {
		Environment::lock('config_monitor', __DIR__ . '/../../../temp/');
		$this->copyConfiguration();
		$this->managerTemp->load(0);
		$this->managerTemp->save($this->values);
		$expectedWebSocket = [
			'component' => 'shape::WebsocketCppService',
			'instance' => 'WebsocketCppService_Monitor',
			'WebsocketPort' => 1339,
			'acceptOnlyLocalhost' => true,
		];
		Assert::same($expectedWebSocket, $this->fileManagerTemp->read($this->fileNames['webSocket']));
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
		Assert::same($expectedMonitor, $this->fileManagerTemp->read($this->fileNames['monitor']));
	}

}

$test = new MonitorManagerTest();
$test->run();
