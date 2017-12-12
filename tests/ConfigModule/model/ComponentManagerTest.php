<?php

/**
 * TEST: App\ConfigModule\Model\ComponentManager
 * @covers App\ConfigModule\Model\ComponentManager
 * @phpVersion >= 5.6
 * @testCase
 */

declare(strict_types=1);

namespace Test\ConfigModule\Model;

use App\ConfigModule\Model\ComponentManager;
use App\Model\JsonFileManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class ComponentManagerTest extends TestCase {

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
	 * Set up test environment
	 */
	public function setUp() {
		$this->fileManager = new JsonFileManager($this->path);
		$this->fileManagerTemp = new JsonFileManager($this->pathTest);
	}

	/**
	 * @test
	 * Test function to load configuration of Components
	 */
	public function testLoad() {
		$manager = new ComponentManager($this->fileManager);
		$json = $this->fileManager->read($this->fileName);
		$expected = $json['Components'];
		Assert::equal($expected, $manager->load());
	}

	/**
	 * @test
	 * Test function to save configuration of Components
	 */
	public function testSave() {
		$manager = new ComponentManager($this->fileManagerTemp);
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
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		$expected['Components'][0]['Enabled'] = false;
		$manager->save(ArrayHash::from($array));
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

	/**
	 * @test
	 * Test function to parse configuration of Components
	 */
	public function testSaveJson() {
		$manager = new ComponentManager($this->fileManager);
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
		$expected = $this->fileManager->read($this->fileName)['Components'];
		Assert::equal($expected, $manager->saveJson(ArrayHash::from($array)));
	}

}

$test = new ComponentManagerTest($container);
$test->run();
