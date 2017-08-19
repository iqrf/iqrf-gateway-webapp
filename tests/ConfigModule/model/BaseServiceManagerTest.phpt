<?php

/**
 * TEST: App\ConfigModule\Model\BaseServiceManager
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\ConfigModule\Model;

use App\ConfigModule\Model\BaseServiceManager;
use App\Model\JsonFileManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class BaseServiceManagerTest extends TestCase {

	/**
	 * @var Container
	 */
	private $container;

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
	 * Constructor
	 * @param Container $container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * @test
	 * Test function to parse configuration of Scheduler
	 */
	public function testLoad() {
		$fileManager = new JsonFileManager($this->path);
		$manager = new BaseServiceManager($fileManager);
		$expected = [
			'Name' => 'BaseServiceForMQ',
			'Messaging' => 'MqMessaging',
			'Serializers' => [
				'SimpleSerializer',
				'JsonSerializer',
			],
		];
		Assert::equal($expected, $manager->load(0));
		Assert::equal([], $manager->load(10));
	}

	/**
	 * @test
	 * Test function to parse configuration of Base services
	 */
	public function testSave() {
		$fileManager = new JsonFileManager($this->pathTest);
		$manager = new BaseServiceManager($fileManager);
		$array = [
			'Name' => 'BaseServiceForMQ',
			'Messaging' => 'MqMessaging',
			'Serializers' => [
				'JsonSerializer',
			],
			'Properties' => [
			],
		];
		$expected = Json::decode(FileSystem::read($this->path . $this->fileName . '.json'), Json::FORCE_ARRAY);
		$fileManager->write($this->fileName, $expected);
		$expected['Instances'][0]['Serializers'] = ['JsonSerializer'];
		$manager->save(ArrayHash::from($array), 0);
		Assert::equal($expected, $fileManager->read($this->fileName));
	}

	/**
	 * @test
	 * Test function to parse configuration of Scheduler
	 */
	public function testSaveJson() {
		$fileManager = new JsonFileManager($this->path);
		$manager = new BaseServiceManager($fileManager);
		$json = Json::decode(FileSystem::read($this->path . $this->fileName . '.json'), Json::FORCE_ARRAY);
		$update = [
			'Name' => 'BaseServiceForMQ1',
			'Messaging' => 'MqMessaging',
			'Serializers' => [
				'SimpleSerializer',
				'JsonSerializer',
			],
			'Properties' => [
			],
		];
		$expected = $json;
		$expected['Instances'][0]['Name'] = 'BaseServiceForMQ1';
		Assert::equal($expected, $manager->saveJson($json, ArrayHash::from($update), 0));
		unset($update['Properties']);
		$expected['Instances'][0]['Properties'] = [];
		Assert::equal($expected, $manager->saveJson($json, ArrayHash::from($update), 0));
	}

}

$test = new BaseServiceManagerTest($container);
$test->run();
