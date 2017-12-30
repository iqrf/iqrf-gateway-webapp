<?php

/**
 * TEST: App\ConfigModule\Model\MainManager
 * @covers App\ConfigModule\Model\MainManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types=1);

namespace Test\ConfigModule\Model;

use App\ConfigModule\Model\MainManager;
use App\Model\JsonFileManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class MainManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManagerTemp;

	/**
	 * @var string File name (without .json)
	 */
	private $fileName = 'config';

	/**
	 * @var string Directory with configuration files
	 */
	private $path = __DIR__ . '/../../configuration/';

	/**
	 * @var string Testing directory with configuration files
	 */
	private $pathTest = __DIR__ . '/../../configuration-test/';

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
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
	 * Test function to load main configuration of daemon
	 */
	public function testLoad() {
		$manager = new MainManager($this->fileManager);
		$expected = $this->fileManager->read($this->fileName);
		Assert::equal($expected, $manager->load());
	}

	/**
	 * @test
	 * Test function to save main configuration of daemon
	 */
	public function testSave() {
		$manager = new MainManager($this->fileManagerTemp);
		$array = [
			'Configuration' => 'v0.0',
			'ConfigurationDir' => '/etc/iqrf-daemon',
			'WatchDogTimeoutMilis' => 10000,
		];
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		$expected['ConfigurationDir'] = '/etc/iqrf-daemon';
		$manager->save(ArrayHash::from($array));
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

}

$test = new MainManagerTest($container);
$test->run();
