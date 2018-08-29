<?php

/**
 * TEST: App\ConfigModule\Model\IqrfManager
 * @covers App\ConfigModule\Model\IqrfManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ConfigModule\Model;

use App\CoreModule\Model\CommandManager;
use App\ConfigModule\Model\IqrfManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for IQRF interface manager
 */
class IqrfManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var \Mockery\MockInterface Mocked command manager
	 */
	private $commandManager;

	/**
	 * @var IqrfManager IQRF interface manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$this->commandManager = \Mockery::mock(CommandManager::class);
		$this->manager = new IqrfManager($this->commandManager);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		\Mockery::close();
	}

	/**
	 * Test function to get list of SPI and USB CDC interfaces available in the system
	 */
	public function testGetCdcInterfaces(): void {
		$output = '/dev/ttyACM0' . PHP_EOL . '/dev/ttyACM1';
		$this->commandManager->shouldReceive('send')->with('ls /dev/ttyACM* | awk \'{ print $0 }\'', true)->andReturn($output);
		$expected = ['/dev/ttyACM0', '/dev/ttyACM1'];
		Assert::same($expected, $this->manager->getCdcInterfaces());
	}

	/**
	 * Test function to get list of SPI and USB CDC interfaces available in the system
	 */
	public function testGetSpiInterfaces(): void {
		$output = '/dev/spidev0.0' . PHP_EOL . '/dev/spidev0.1' . PHP_EOL . '/dev/spidev1.0' . PHP_EOL . '/dev/spidev1.1';
		$this->commandManager->shouldReceive('send')->with('ls /dev/spidev* | awk \'{ print $0 }\'', true)->andReturn($output);
		$expected = ['/dev/spidev0.0', '/dev/spidev0.1', '/dev/spidev1.0', '/dev/spidev1.1'];
		Assert::same($expected, $this->manager->getSpiInterfaces());
	}

}

$test = new IqrfManagerTest($container);
$test->run();
