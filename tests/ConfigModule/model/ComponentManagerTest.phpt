<?php

/**
 * TEST: App\ConfigModule\Model\ComponentManager
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\ConfigModule\Model;

use App\ConfigModule\Model\ComponentManager;
use App\Model\JsonFileManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class ComponentManagerTest extends TestCase {

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var string
	 */
	private $fileName = 'config';

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
	 * Test function to save configuration of Components
	 */
	public function testSave() {
		$fileManager = new JsonFileManager($this->pathTest);
		$manager = new ComponentManager($fileManager);
		$array = [
			'TracerFile' => false,
			'IqrfInterface' => true,
			'UdpMessaging' => true,
			'MqttMessaging' => true,
			'MqMessaging' => true,
			'Scheduler' => true,
			'SimpleSerializer' => true,
			'JsonSerializer' => true,
			'BaseService' => true,
		];
		$expected = Json::decode(FileSystem::read($this->path . $this->fileName . '.json'), Json::FORCE_ARRAY);
		$fileManager->write($this->fileName, $expected);
		$expected['Components'][0]['Enabled'] = false;
		$manager->save(ArrayHash::from($array));
		Assert::equal($expected, $fileManager->read($this->fileName));
	}

	/**
	 * @test
	 * Test function to parse configuration of Components
	 */
	public function testSaveJson() {
		$fileManager = new JsonFileManager($this->path);
		$manager = new ComponentManager($fileManager);
		$array = [
			'TracerFile' => true,
			'IqrfInterface' => true,
			'UdpMessaging' => true,
			'MqttMessaging' => true,
			'MqMessaging' => true,
			'Scheduler' => true,
			'SimpleSerializer' => true,
			'JsonSerializer' => true,
			'BaseService' => true,
		];
		$expected = Json::decode(FileSystem::read($this->path . $this->fileName . '.json'), Json::FORCE_ARRAY)['Components'];
		Assert::equal($expected, $manager->saveJson(ArrayHash::from($array)));
	}

}

$test = new ComponentManagerTest($container);
$test->run();
