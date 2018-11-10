<?php

/**
 * TEST: App\IqrfNetModule\Models\IqrfNetManager
 * @covers App\IqrfNetModule\Models\IqrfNetManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\IqrfNetModule\Models;

use App\IqrfNetModule\Exceptions as IqrfException;
use App\IqrfNetModule\Models\DpaRawManager;
use App\IqrfNetModule\Models\IqrfAppManager;
use App\IqrfNetModule\Models\IqrfNetManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for IQMESH Network manager
 */
class IqrfNetManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var \Mockery\MockInterface Mocked IQRF App manager
	 */
	private $dpaManageer;

	/**
	 * @var IqrfNetManager IQMESH Network manager
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
	 * Test function to clear all bonds
	 */
	public function testClearAllBonds(): void {
		$packet = '00.00.00.03.ff.ff';
		$this->dpaManageer->shouldReceive('send')->with($packet)->andReturn([true]);
		Assert::same([true], $this->manager->clearAllBonds());
	}

	/**
	 * Test function to bond new node
	 */
	public function testBondNode(): void {
		$packet0 = '00.00.00.04.ff.ff.00.00';
		$this->dpaManageer->shouldReceive('send')->with($packet0, 12000)->andReturn([true]);
		Assert::same([true], $this->manager->bondNode());
		$packet1 = '00.00.00.04.ff.ff.0f.00';
		$this->dpaManageer->shouldReceive('send')->with($packet1, 12000)->andReturn([true]);
		Assert::same([true], $this->manager->bondNode('f'));
	}

	/**
	 * Test function to discovery IQMESH Network
	 */
	public function testDiscovery(): void {
		$packet0 = '00.00.00.07.ff.ff.00.00';
		$this->dpaManageer->shouldReceive('send')->with($packet0, 0)->andReturn([true]);
		Assert::same([true], $this->manager->discovery());
		$packet1 = '00.00.00.07.ff.ff.06.ef';
		$this->dpaManageer->shouldReceive('send')->with($packet1, 0)->andReturn([true]);
		Assert::same([true], $this->manager->discovery(6, 'ef'));
	}

	/**
	 * Test function to rebond node
	 */
	public function testRebondNode(): void {
		$packet = '00.00.00.06.ff.ff.10';
		$this->dpaManageer->shouldReceive('send')->with($packet)->andReturn([true]);
		Assert::same([true], $this->manager->rebondNode('10'));
	}

	/**
	 * Test function to remove the node
	 */
	public function testRemoveNode(): void {
		$packet = '00.00.00.05.ff.ff.10';
		$this->dpaManageer->shouldReceive('send')->with($packet)->andReturn([true]);
		Assert::same([true], $this->manager->removeNode('10'));
	}

	/**
	 * Test function to read HWP configuration
	 */
	public function testReadHwpConfiguration(): void {
		$packet = '00.00.02.02.ff.ff.';
		$this->dpaManageer->shouldReceive('send')->with($packet)->andReturn(['response']);
		$this->dpaManageer->shouldReceive('parseResponse')->with(['response'])->andReturn([true]);
		Assert::same([true], $this->manager->readHwpConfiguration());
	}

	/**
	 * Test function to write HWP configuration byte
	 */
	public function testWriteHwpConfigurationByte(): void {
		$packet = '00.00.02.09.ff.ff.06.34.ff';
		$this->dpaManageer->shouldReceive('send')->with($packet)->andReturn([true]);
		Assert::same([true], $this->manager->writeHwpConfigurationByte('06', '34'));
	}

	/**
	 * Test function to set RF channel
	 */
	public function testSetRfChannel(): void {
		$packet0 = '00.00.02.09.ff.ff.11.34.ff';
		$packet1 = '00.00.02.09.ff.ff.12.40.ff';
		$packet2 = '00.00.02.09.ff.ff.06.1a.ff';
		$packet3 = '00.00.02.09.ff.ff.07.20.ff';
		$this->dpaManageer->shouldReceive('send')->with($packet0)->andReturn([0]);
		$this->dpaManageer->shouldReceive('send')->with($packet1)->andReturn([1]);
		$this->dpaManageer->shouldReceive('send')->with($packet2)->andReturn([2]);
		$this->dpaManageer->shouldReceive('send')->with($packet3)->andReturn([3]);
		Assert::same([0], $this->manager->setRfChannel(52, IqrfNetManager::MAIN_RF_CHANNEL_A));
		Assert::same([1], $this->manager->setRfChannel(64, IqrfNetManager::MAIN_RF_CHANNEL_B));
		Assert::same([2], $this->manager->setRfChannel(26, IqrfNetManager::ALTERNATIVE_RF_CHANNEL_A));
		Assert::same([3], $this->manager->setRfChannel(32, IqrfNetManager::ALTERNATIVE_RF_CHANNEL_B));
		Assert::exception(function (): void {
			$this->manager->setRfChannel(52, 'test');
		}, IqrfException\InvalidRfChannelTypeException::class);
	}

	/**
	 * Test function to set RF LP timeout
	 */
	public function testSetRfLpTimeout(): void {
		$packet0 = '00.00.02.09.ff.ff.0a.01.ff';
		$packet1 = '00.00.02.09.ff.ff.0a.ff.ff';
		$this->dpaManageer->shouldReceive('send')->with($packet0)->andReturn([0]);
		$this->dpaManageer->shouldReceive('send')->with($packet1)->andReturn([1]);
		Assert::same([0], $this->manager->setRfLpTimeout(1));
		Assert::same([1], $this->manager->setRfLpTimeout(255));
		Assert::exception(function (): void {
			$this->manager->setRfLpTimeout(0);
		}, IqrfException\InvalidRfLpTimeoutException::class);
		Assert::exception(function (): void {
			$this->manager->setRfLpTimeout(256);
		}, IqrfException\InvalidRfLpTimeoutException::class);
	}

	/**
	 * Test function to set RF output power
	 */
	public function testSetRfOutputPower(): void {
		$packet0 = '00.00.02.09.ff.ff.08.00.ff';
		$packet1 = '00.00.02.09.ff.ff.08.07.ff';
		$this->dpaManageer->shouldReceive('send')->with($packet0)->andReturn([0]);
		$this->dpaManageer->shouldReceive('send')->with($packet1)->andReturn([1]);
		Assert::same([0], $this->manager->setRfOutputPower(0));
		Assert::same([1], $this->manager->setRfOutputPower(7));
		Assert::exception(function (): void {
			$this->manager->setRfOutputPower(-1);
		}, IqrfException\InvalidRfOutputPowerException::class);
		Assert::exception(function (): void {
			$this->manager->setRfOutputPower(8);
		}, IqrfException\InvalidRfOutputPowerException::class);
	}

	/**
	 * Test function to set RF signal filter
	 */
	public function testSetRfSignalFilter(): void {
		$packet0 = '00.00.02.09.ff.ff.09.00.ff';
		$packet1 = '00.00.02.09.ff.ff.09.40.ff';
		$this->dpaManageer->shouldReceive('send')->with($packet0)->andReturn([0]);
		$this->dpaManageer->shouldReceive('send')->with($packet1)->andReturn([1]);
		Assert::same([0], $this->manager->setRfSignalFilter(0));
		Assert::same([1], $this->manager->setRfSignalFilter(64));
		Assert::exception(function (): void {
			$this->manager->setRfSignalFilter(-1);
		}, IqrfException\InvalidRfSignalFilterException::class);
		Assert::exception(function (): void {
			$this->manager->setRfSignalFilter(65);
		}, IqrfException\InvalidRfSignalFilterException::class);
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$this->dpaManageer = \Mockery::mock(DpaRawManager::class);
		$this->manager = new IqrfNetManager($this->dpaManageer);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		\Mockery::close();
	}

}

$test = new IqrfNetManagerTest($container);
$test->run();
