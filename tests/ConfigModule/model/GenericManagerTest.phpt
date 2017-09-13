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
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class GenericManagerTest extends TestCase {

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
		$manager = new GenericManager($this->fileManager);
		$expected = $this->fileManager->read($this->fileName);
		Assert::equal($expected, $manager->load($this->fileName));
	}

	/**
	 * @test
	 * Test function to save main configuration of daemon
	 */
	public function testSave() {
		$manager = new GenericManager($this->fileManagerTemp);
		$array = [
			'TraceFileName' => '',
			'TraceFileSize' => 0,
			'VerbosityLevel' => 'err'
		];
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		$expected['VerbosityLevel'] = 'err';
		$manager->save($this->fileName, ArrayHash::from($array));
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

}

$test = new GenericManagerTest($container);
$test->run();
