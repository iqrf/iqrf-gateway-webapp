<?php

/**
 * TEST: App\ConfigModule\Model\IqrfManager
 * @covers App\ConfigModule\Model\IqrfManager
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\ConfigModule\Model;

use App\Model\CommandManager;
use App\ConfigModule\Model\IqrfManager;
use App\Model\JsonFileManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class IqrfManagerTest extends TestCase {

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var CommandManager
	 */
	private $commandManager;

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
	private $fileName = 'IqrfInterface';

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
		$this->commandManager = new CommandManager(false);
		$this->fileManager = new JsonFileManager($this->path);
		$this->fileManagerTemp = new JsonFileManager($this->pathTest);
	}

	/**
	 * @test
	 * Test function to load main configuration of daemon
	 */
	public function testLoad() {
		$manager = new IqrfManager($this->commandManager, $this->fileManager);
		$expected = $this->fileManager->read($this->fileName);
		Assert::equal($expected, $manager->load());
	}

	/**
	 * @test
	 * Test function to save main configuration of daemon
	 */
	public function testSave() {
		$manager = new IqrfManager($this->commandManager, $this->fileManagerTemp);
		$array = [
			'IqrfInterface' => 'COM6',
			'DpaHandlerTimeout' => 500,
			'CommunicationMode' => 'LP',
		];
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		$expected['CommunicationMode'] = 'LP';
		$manager->save(ArrayHash::from($array));
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

	/**
	 * @test
	 * Test function to get list of SPI and USB CDC interfaces available in the system
	 */
	public function testGetInterfaces() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$outputCdc = '/dev/ttyACM0' . PHP_EOL . '/dev/ttyACM1';
		$outputSpi = '/dev/spidev0.0' . PHP_EOL . '/dev/spidev0.1' . PHP_EOL . '/dev/spidev1.0' . PHP_EOL . '/dev/spidev1.1';
		$commandManager->shouldReceive('send')->with('ls /dev/ttyACM* | awk \'{ print $0 }\'', true)->andReturn($outputCdc);
		$commandManager->shouldReceive('send')->with('ls /dev/spidev* | awk \'{ print $0 }\'', true)->andReturn($outputSpi);
		$manager = new IqrfManager($commandManager, $this->fileManager);
		$expected = [
			'cdc' => ['/dev/ttyACM0', '/dev/ttyACM1'],
			'spi' => ['/dev/spidev0.0', '/dev/spidev0.1', '/dev/spidev1.0', '/dev/spidev1.1']
		];
		Assert::same($expected, $manager->getInterfaces());
	}

	/**
	 * @test
	 * Test function to get list of SPI and USB CDC interfaces available in the system
	 */
	public function testGetCdcInterfaces() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$output = '/dev/ttyACM0' . PHP_EOL . '/dev/ttyACM1';
		$commandManager->shouldReceive('send')->with('ls /dev/ttyACM* | awk \'{ print $0 }\'', true)->andReturn($output);
		$manager = new IqrfManager($commandManager, $this->fileManager);
		$expected = ['/dev/ttyACM0', '/dev/ttyACM1'];
		Assert::same($expected, $manager->getCdcInterfaces());
	}

	/**
	 * @test
	 * Test function to get list of SPI and USB CDC interfaces available in the system
	 */
	public function testGetSpiInterfaces() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$output = '/dev/spidev0.0' . PHP_EOL . '/dev/spidev0.1' . PHP_EOL . '/dev/spidev1.0' . PHP_EOL . '/dev/spidev1.1';
		$commandManager->shouldReceive('send')->with('ls /dev/spidev* | awk \'{ print $0 }\'', true)->andReturn($output);
		$manager = new IqrfManager($commandManager, $this->fileManager);
		$expected = ['/dev/spidev0.0', '/dev/spidev0.1', '/dev/spidev1.0', '/dev/spidev1.1'];
		Assert::same($expected, $manager->getSpiInterfaces());
	}

}

$test = new IqrfManagerTest($container);
$test->run();
