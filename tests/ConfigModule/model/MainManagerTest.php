<?php

/**
 * TEST: App\ConfigModule\Model\MainManager
 * @covers App\ConfigModule\Model\MainManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ConfigModule\Model;

use App\ConfigModule\Model\MainManager;
use App\Model\JsonFileManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for main configuration manager
 */
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
	private $fileManagerTest;

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
		$this->fileManagerTest = new JsonFileManager($this->pathTest);
	}

	/**
	 * Test function to load main configuration of daemon
	 */
	public function testLoad() {
		$manager = new MainManager($this->fileManager);
		$expected = $this->fileManager->read($this->fileName);
		Assert::equal($expected, $manager->load());
	}

	/**
	 * Test function to save main configuration of daemon
	 */
	public function testSave() {
		$manager = new MainManager($this->fileManagerTest);
		$array = [
			'applicationName' => 'IqrfGatewayDaemon',
			'resourceDir' => '',
			'dataDir' => '/usr/share/iqrfgd2',
			'cacheDir' => '/var/cache/iqrfgd2',
			'userDir' => '',
			'configurationDir' => '/etc/iqrf-daemon',
			'deploymentDir' => '/usr/lib/iqrfgd2',
		];
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTest->write($this->fileName, $expected);
		$expected['configurationDir'] = '/etc/iqrf-daemon';
		$manager->save($array);
		Assert::equal($expected, $this->fileManagerTest->read($this->fileName));
	}

}

$test = new MainManagerTest($container);
$test->run();
