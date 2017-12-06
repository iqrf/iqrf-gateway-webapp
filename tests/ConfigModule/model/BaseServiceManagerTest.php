<?php

/**
 * TEST: App\ConfigModule\Model\BaseServiceManager
 * @covers App\ConfigModule\Model\BaseServiceManager
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\ConfigModule\Model;

use App\ConfigModule\Model\BaseServiceManager;
use App\Model\JsonFileManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class BaseServiceManagerTest extends TestCase {

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var JsonFileManager
	 */
	private $fileManager;

	/**
	 * @var JsonFileManager
	 */
	private $fileManagerTemp;

	/**
	 * @var string
	 */
	private $fileName = 'BaseService';

	/**
	 * @var string
	 */
	private $path = __DIR__ . '/../../configuration/';

	/**
	 * @var string
	 */
	private $pathTest = __DIR__ . '/../../configuration-test/';

	/**
	 * @var array
	 */
	private $services = [
		[
			'Name' => 'BaseServiceForMQ',
			'Messaging' => 'MqMessaging',
			'Serializers' => ['SimpleSerializer', 'JsonSerializer'],
			'Properties' => ['AsyncDpaMessage' => false],
		],
		[
			'Name' => 'BaseServiceForMQTT1',
			'Messaging' => 'MqttMessaging1',
			'Serializers' => ['JsonSerializer'],
			'Properties' => ['AsyncDpaMessage' => true],
		],
		[
			'Name' => 'BaseServiceForMQTT2',
			'Messaging' => 'MqttMessaging2',
			'Serializers' => ['JsonSerializer'],
			'Properties' => ['AsyncDpaMessage' => true],
		],
	];

	/**
	 * Constructor
	 * @param Container $container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Set up test environment
	 */
	public function setUp() {
		$this->fileManager = new JsonFileManager($this->path);
		$this->fileManagerTemp = new JsonFileManager($this->pathTest);
	}

	/**
	 * @test
	 * Test function to delete configuration of Base services
	 */
	public function testDelete() {
		$manager = new BaseServiceManager($this->fileManagerTemp);
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		unset($expected['Instances'][2]);
		$manager->delete(2);
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

	/**
	 * @test
	 * Test function to get list of Base services
	 */
	public function testGetServices() {
		$manager = new BaseServiceManager($this->fileManager);
		Assert::equal($this->services, $manager->getServices());
	}

	/**
	 * @test
	 * Test function to parse configuration of Scheduler
	 */
	public function testLoad() {
		$manager = new BaseServiceManager($this->fileManager);
		$expected = $this->services[0];
		Assert::equal($expected, $manager->load(0));
		Assert::equal([], $manager->load(10));
	}

	/**
	 * @test
	 * Test function to parse configuration of Base services
	 */
	public function testSave() {
		$manager = new BaseServiceManager($this->fileManagerTemp);
		$array = $this->services[0];
		$array['Serializers'] = ['JsonSerializer'];
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		$expected['Instances'][0]['Serializers'] = ['JsonSerializer'];
		$manager->save(ArrayHash::from($array), 0);
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

	/**
	 * @test
	 * Test function to parse configuration of Scheduler
	 */
	public function testSaveJson() {
		$manager = new BaseServiceManager($this->fileManager);
		$json = $this->fileManager->read($this->fileName);
		$update = $this->services[0];
		$update['Name'] = 'BaseServiceForMQ1';
		$expected = $json;
		$expected['Instances'][0]['Name'] = 'BaseServiceForMQ1';
		Assert::equal($expected, $manager->saveJson($json, ArrayHash::from($update), 0));
		$update['Instances'] = [];
		unset($update['Properties']);
		$expected['Instances'][0]['Properties'] = [];
		Assert::equal($expected, $manager->saveJson($json, ArrayHash::from($update), 0));
	}

}

$test = new BaseServiceManagerTest($container);
$test->run();
