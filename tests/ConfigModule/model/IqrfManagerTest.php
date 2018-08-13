<?php

/**
 * TEST: App\ConfigModule\Model\IqrfManager
 * @covers App\ConfigModule\Model\IqrfManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ConfigModule\Model;

use App\Model\CommandManager;
use App\ConfigModule\Model\IqrfManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class IqrfManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

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
		$this->commandManager = new CommandManager(false);
	}

	/**
	 * Test function to get list of SPI and USB CDC interfaces available in the system
	 */
	public function testGetCdcInterfaces() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$output = '/dev/ttyACM0' . PHP_EOL . '/dev/ttyACM1';
		$commandManager->shouldReceive('send')->with('ls /dev/ttyACM* | awk \'{ print $0 }\'', true)->andReturn($output);
		$manager = new IqrfManager($commandManager);
		$expected = ['/dev/ttyACM0', '/dev/ttyACM1'];
		Assert::same($expected, $manager->getCdcInterfaces());
	}

	/**
	 * Test function to get list of SPI and USB CDC interfaces available in the system
	 */
	public function testGetSpiInterfaces() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$output = '/dev/spidev0.0' . PHP_EOL . '/dev/spidev0.1' . PHP_EOL . '/dev/spidev1.0' . PHP_EOL . '/dev/spidev1.1';
		$commandManager->shouldReceive('send')->with('ls /dev/spidev* | awk \'{ print $0 }\'', true)->andReturn($output);
		$manager = new IqrfManager($commandManager);
		$expected = ['/dev/spidev0.0', '/dev/spidev0.1', '/dev/spidev1.0', '/dev/spidev1.1'];
		Assert::same($expected, $manager->getSpiInterfaces());
	}

}

$test = new IqrfManagerTest($container);
$test->run();
