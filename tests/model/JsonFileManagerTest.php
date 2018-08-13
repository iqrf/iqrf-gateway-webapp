<?php

/**
 * TEST: App\Model\JsonFileManager
 * @covers App\Model\JsonFileManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\Model;

use App\Model\JsonFileManager;
use Nette\DI\Container;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

class JsonFileManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;
	* @test

	/**
	 * @var string Directory with configuration files
	 */
	private $path = __DIR__ . '/../configuration/';

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Test function to read configuration file
	 */
	public function testRead() {
		$configManager = new JsonFileManager($this->path);
		$expected = Json::decode(FileSystem::read($this->path . 'config.json'), Json::FORCE_ARRAY);
		Assert::equal($expected, $configManager->read('config'));
	}

	/**
	 * Test function to write configuration file
	 */
	public function testWrite() {
		$path = __DIR__ . '/../configuration-test/';
		$fileName = 'config-test';
		$configManager = new JsonFileManager($path);
		$expected = Json::decode(FileSystem::read($this->path . 'config.json'), Json::FORCE_ARRAY);
		$configManager->write($fileName, $expected);
		Assert::equal($expected, $configManager->read($fileName));
	}

}

$test = new JsonFileManagerTest($container);
$test->run();
