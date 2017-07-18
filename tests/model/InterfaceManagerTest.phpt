<?php

/**
 * TEST: App\Model\InterfaceManager
 * @phpVersion >= 5.6
 * @testCase
 */
use App\Model\InterfaceManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

class InterfaceManagerTest extends TestCase {

	private $container;

	function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * @test
	 * Test function to get list of SPI and USB CDC interfaces available in the system
	 */
	public function testCreateInterfaceList() {
		$commandManager = \Mockery::mock('App\Model\CommandManager');
		$outputCdc = '/dev/ttyACM0' . PHP_EOL . '/dev/ttyACM1';
		$outputSpi = '/dev/spidev0.0' . PHP_EOL . '/dev/spidev0.1' . PHP_EOL . '/dev/spidev1.0' . PHP_EOL . '/dev/spidev1.1';
		$commandManager->shouldReceive('send')->with("ls /dev/ttyACM* | awk '{ print $0 }'", true)->andReturn($outputCdc);
		$commandManager->shouldReceive('send')->with("ls /dev/spidev* | awk '{ print $0 }'", true)->andReturn($outputSpi);
		$interfaceManager = new InterfaceManager($commandManager);
		$expected = ['cdc' => ['/dev/ttyACM0', '/dev/ttyACM1'], 'spi' => ['/dev/spidev0.0', '/dev/spidev0.1', '/dev/spidev1.0', '/dev/spidev1.1']];
		Assert::same($expected, $interfaceManager->createInterfaceList());
	}

}

$test = new InterfaceManagerTest($container);
$test->run();
