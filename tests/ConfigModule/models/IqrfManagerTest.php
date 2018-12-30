<?php

/**
 * TEST: App\ConfigModule\Models\IqrfManager
 * @covers App\ConfigModule\Models\IqrfManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ConfigModule\Models;

use App\ConfigModule\Models\IqrfManager;
use App\CoreModule\Models\CommandManager;
use Mockery;
use Mockery\MockInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for IQRF interface manager
 */
class IqrfManagerTest extends TestCase {

	/**
	 * @var MockInterface Mocked command manager
	 */
	private $commandManager;

	/**
	 * @var IqrfManager IQRF interface manager
	 */
	private $manager;

	/**
	 * Test function to get list of USB CDC interfaces available in the system
	 */
	public function testGetCdcInterfaces(): void {
		$output = '/dev/ttyACM0' . PHP_EOL . '/dev/ttyACM1';
		$this->commandManager->shouldReceive('send')->with('ls /dev/ttyACM* | awk \'{ print $0 }\'', true)->andReturn($output);
		$expected = ['/dev/ttyACM0', '/dev/ttyACM1'];
		Assert::same($expected, $this->manager->getCdcInterfaces());
	}

	/**
	 * Test function to get list of SPI interfaces available in the system
	 */
	public function testGetSpiInterfaces(): void {
		$output = '/dev/spidev0.0' . PHP_EOL . '/dev/spidev0.1' . PHP_EOL . '/dev/spidev1.0' . PHP_EOL . '/dev/spidev1.1';
		$this->commandManager->shouldReceive('send')->with('ls /dev/spidev* | awk \'{ print $0 }\'', true)->andReturn($output);
		$expected = ['/dev/spidev0.0', '/dev/spidev0.1', '/dev/spidev1.0', '/dev/spidev1.1'];
		Assert::same($expected, $this->manager->getSpiInterfaces());
	}

	/**
	 * Test function to get list of UART interfaces available in the system
	 */
	public function testGetUartInterfaces(): void {
		$output = '/dev/ttyS0' . PHP_EOL . '/dev/ttyS1' . PHP_EOL . '/dev/ttyS2' . PHP_EOL . '/dev/ttyS3';
		$this->commandManager->shouldReceive('send')->with('ls /dev/ttyAMA* /dev/ttyS* | awk \'{ print $0 }\'', true)->andReturn($output);
		$expected = ['/dev/ttyS0', '/dev/ttyS1', '/dev/ttyS2', '/dev/ttyS3'];
		Assert::same($expected, $this->manager->getUartInterfaces());
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$this->commandManager = Mockery::mock(CommandManager::class);
		$this->manager = new IqrfManager($this->commandManager);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		Mockery::close();
	}

}

$test = new IqrfManagerTest();
$test->run();
