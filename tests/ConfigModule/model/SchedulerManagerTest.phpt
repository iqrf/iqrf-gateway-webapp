<?php

/**
 * TEST: App\ConfigModule\Model\SchedulerManager
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\ConfigModule\Model;

use App\ConfigModule\Model\SchedulerManager;
use App\Model\JsonFileManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class SchedulerManagerTest extends TestCase {

	/**
	 * @var Container
	 */
	private $container;

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
		$manager = new SchedulerManager($fileManager);
		$expected = [
			'time' => '*/5 * * * * * *',
			'service' => 'BaseServiceForMQTT1',
			'ctype' => 'dpa',
			'type' => 'std-sen',
			'nadr' => '1',
			'cmd' => 'READ',
			'hwpid' => 'ffff',
			'sensors' => "Temperature1\nCO2_1\nHumidity1",
		];
		Assert::equal($expected, $manager->load(0));
		Assert::equal([], $manager->load(10));
	}

	/**
	 * @test
	 * Test function to parse configuration of Scheduler
	 */
	public function testSave() {
		$fileManager = new JsonFileManager($this->pathTest);
		$manager = new SchedulerManager($fileManager);
		$fileName = 'Scheduler';
		$array = [
			'time' => '*/5 * * * * * *',
			'service' => 'BaseServiceForMQTT1',
			'ctype' => 'dpa',
			'type' => 'std-sen',
			'nadr' => '0',
			'cmd' => 'READ',
			'hwpid' => 'ffff',
			'sensors' => 'Temperature1' . PHP_EOL . 'CO2_1' . PHP_EOL . 'Humidity1',
		];
		$expected = Json::decode(FileSystem::read($this->path . 'Scheduler.json'), Json::FORCE_ARRAY);
		$fileManager->write($fileName, $expected);
		$expected['TasksJson'][0]['message']['nadr'] = '0';
		$manager->save(ArrayHash::from($array), 0);
		Assert::equal($expected, $fileManager->read($fileName));
	}

	/**
	 * @test
	 * Test function to parse configuration of Scheduler
	 */
	public function testSaveJson() {
		$fileManager = new JsonFileManager($this->path);
		$manager = new SchedulerManager($fileManager);
		$updateArray = [
			'time' => '*/5 * * * * * *',
			'service' => 'BaseServiceForMQTT1',
			'ctype' => 'dpa',
			'type' => 'std-sen',
			'nadr' => '0',
			'cmd' => 'READ',
			'hwpid' => 'ffff',
			'sensors' => "Temperature1\nCO2_1\nHumidity1",
		];
		$json = Json::decode(FileSystem::read($this->path . 'Scheduler.json'), Json::FORCE_ARRAY);
		$expected = $json;
		$expected['TasksJson'][0]['message']['nadr'] = '0';
		$update = ArrayHash::from($updateArray);
		Assert::equal($expected, $manager->saveJson($json, $update, 0));
	}

}

$test = new SchedulerManagerTest($container);
$test->run();
