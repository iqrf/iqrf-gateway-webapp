<?php

/**
 * TEST: App\ConfigModule\Model\GenericManager
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\ConfigModule\Model;

use App\ConfigModule\Model\GenericManager;
use App\Model\JsonFileManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class GenericManagerTest extends TestCase {

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var string
	 */
	private $fileName = 'TracerFile';

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
	 * Test function to load main configuration of daemon
	 */
	public function testLoad() {
		$fileManager = new JsonFileManager($this->path);
		$manager = new GenericManager($fileManager);
		$expected = Json::decode(FileSystem::read($this->path . $this->fileName . '.json'), Json::FORCE_ARRAY);
		Assert::equal($expected, $manager->load($this->fileName));
	}

	/**
	 * @test
	 * Test function to save main configuration of daemon
	 */
	public function testSave() {
		$fileManager = new JsonFileManager($this->pathTest);
		$manager = new GenericManager($fileManager);
		$array = [
			'TraceFileName' => '',
			'TraceFileSize' => 0,
			'VerbosityLevel' => 'err'
		];
		$expected = Json::decode(FileSystem::read($this->path . $this->fileName . '.json'), Json::FORCE_ARRAY);
		$fileManager->write($this->fileName, $expected);
		$expected['VerbosityLevel'] = 'err';
		$manager->save($this->fileName, ArrayHash::from($array));
		Assert::equal($expected, $fileManager->read($this->fileName));
	}

}

$test = new GenericManagerTest($container);
$test->run();
