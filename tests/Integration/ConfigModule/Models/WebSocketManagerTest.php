<?php

/**
 * TEST: App\ConfigModule\Models\WebSocketManager
 * @covers App\ConfigModule\Models\WebSocketManager
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Integration\ConfigModule\Models;

use App\ConfigModule\Models\GenericManager;
use App\ConfigModule\Models\WebSocketManager;
use Tester\Assert;
use Tester\Environment;
use Tests\Toolkit\TestCases\JsonConfigTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for WebSocket interface configuration manager
 */
final class WebSocketManagerTest extends JsonConfigTestCase {

	/**
	 * WebSocket messaging and service file names
	 */
	private const FILE_NAMES = [
		'messaging' => 'iqrf__WebsocketMessaging',
		'service' => 'shape__WebsocketCppService',
	];

	/**
	 * WebSocket instances
	 */
	private const INSTANCES = [
		'messaging' => 'WebsocketMessaging',
		'service' => 'WebsocketCppService',
	];

	/**
	 * Values from configuration form
	 */
	private const VALUES = [
		'acceptAsyncMsg' => true,
		'port' => 1338,
		'acceptOnlyLocalhost' => true,
	];

	/**
	 * @var WebSocketManager WebSocket interface configuration manager
	 */
	private $manager;

	/**
	 * @var WebSocketManager WebSocket interface configuration manager
	 */
	private $managerTemp;

	/**
	 * Tests the function to delete a WebSocket interface configuration
	 */
	public function testDelete(): void {
		Environment::lock('config_websocket', __DIR__ . '/../../../temp/');
		$this->copyConfiguration();
		Assert::true($this->fileManagerTemp->exists(self::FILE_NAMES['messaging']));
		Assert::true($this->fileManagerTemp->exists(self::FILE_NAMES['service']));
		$this->managerTemp->delete(0);
		Assert::false($this->fileManagerTemp->exists(self::FILE_NAMES['messaging']));
		Assert::false($this->fileManagerTemp->exists(self::FILE_NAMES['service']));
	}

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
	 * Tests the function to load a WebSocket interface configuration
	 */
	public function testLoad(): void {
		$expected = [
			'messagingInstance' => self::INSTANCES['messaging'],
			'acceptAsyncMsg' => true,
			'serviceInstance' => self::INSTANCES['service'],
			'port' => 1338,
			'acceptOnlyLocalhost' => false,
		];
		Assert::same($expected, $this->manager->load(0));
	}

	/**
	 * Tests the function to save a WebSocket interface
	 */
	public function testSave(): void {
		Environment::lock('config_websocket', __DIR__ . '/../../../temp/');
		$this->copyConfiguration();
		$this->managerTemp->load(0);
		$this->managerTemp->save(self::VALUES);
		$expectedMessaging = [
			'component' => 'iqrf::WebsocketMessaging',
			'instance' => 'WebsocketMessaging',
			'acceptAsyncMsg' => true,
			'RequiredInterfaces' => [
				[
					'name' => 'shape::IWebsocketService',
					'target' => ['instance' => 'WebsocketCppService'],
				],
			],
		];
		Assert::same($expectedMessaging, $this->fileManagerTemp->read(self::FILE_NAMES['messaging']));
		$expectedService = [
			'component' => 'shape::WebsocketCppService',
			'instance' => 'WebsocketCppService',
			'WebsocketPort' => 1338,
			'acceptOnlyLocalhost' => true,
		];
		Assert::same($expectedService, $this->fileManagerTemp->read(self::FILE_NAMES['service']));
	}

	/**
	 * Tests the function to get a WebSocket interfaces
	 */
	public function testList(): void {
		$expected = [
			[
				'id' => 0,
				'messagingInstance' => 'WebsocketMessaging',
				'acceptAsyncMsg' => true,
				'serviceInstance' => 'WebsocketCppService',
				'port' => 1338,
				'acceptOnlyLocalhost' => false,
			],
		];
		Assert::equal($expected, $this->manager->list());
	}

	/**
	 * Tests the function to get WebSocket service file name by instance name
	 */
	public function testGetServiceFile(): void {
		Assert::same(self::FILE_NAMES['service'], $this->manager->getServiceFile(self::INSTANCES['service']));
		Assert::null($this->manager->getServiceFile('foobar'));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$genericManager = new GenericManager($this->fileManager, $this->schemaManager);
		$genericManagerTest = new GenericManager($this->fileManagerTemp, $this->schemaManager);
		$this->manager = new WebSocketManager($genericManager, $this->schemaManager);
		$this->managerTemp = new WebSocketManager($genericManagerTest, $this->schemaManager);
	}

}

$test = new WebSocketManagerTest();
$test->run();
