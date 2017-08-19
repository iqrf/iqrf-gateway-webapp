<?php

/**
 * TEST: App\ConfigModule\Model\MainManager
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\ConfigModule\Model;

use App\ConfigModule\Model\MainManager;
use App\Model\JsonFileManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class MainManagerTest extends TestCase {

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
	 * Test function to save configuration of Mains
	 */
	public function testSave() {
		$fileManager = new JsonFileManager($this->pathTest);
		$manager = new MainManager($fileManager);
		$array = [
			'Configuration' => 'v0.0',
			'ConfigurationDir' => '/etc/iqrf-daemon',
			'WatchDogTimeoutMilis' => 10000,
		];
		$expected = Json::decode(FileSystem::read($this->path . $this->fileName . '.json'), Json::FORCE_ARRAY);
		$fileManager->write($this->fileName, $expected);
		$expected['ConfigurationDir'] = '/etc/iqrf-daemon';
		$manager->save(ArrayHash::from($array));
		Assert::equal($expected, $fileManager->read($this->fileName));
	}

}

$test = new MainManagerTest($container);
$test->run();
