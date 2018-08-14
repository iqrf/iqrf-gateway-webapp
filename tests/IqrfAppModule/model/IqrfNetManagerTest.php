<?php

/**
 * TEST: App\IqrfAppModule\Model\IqrfNetManager
 * @covers App\IqrfAppModule\Model\IqrfNetManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\IqrfAppModule\Model;

use App\IqrfAppModule\Model\IqrfAppManager;
use App\IqrfAppModule\Model\IqrfNetManager;
use App\IqrfAppModule\Model\InvalidRfChannelTypeException;
use App\IqrfAppModule\Model\InvalidRfLpTimeoutException;
use App\IqrfAppModule\Model\InvalidRfOutputPowerException;
use App\IqrfAppModule\Model\InvalidRfSignalFilterException;
use App\IqrfAppModule\Model\UnsupportedInputFormatException;
use App\IqrfAppModule\Model\UnsupportedSecurityTypeException;
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
	private $iqrfAppManager;

	/**
	 * @var IqrfNetManager IQMESH Network manager
	 */
	private $iqrfNetManager;

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
		$this->iqrfAppManager = \Mockery::mock(IqrfAppManager::class);
		$this->iqrfNetManager = new IqrfNetManager($this->iqrfAppManager);
	}

	/**
	 * Test function to clear all bonds
	 */
	public function testClearAllBonds() {
		$packet = '00.00.00.03.ff.ff';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet)->andReturn([true]);
		Assert::same([true], $this->iqrfNetManager->clearAllBonds());
	}

	/**
	 * Test function to bond new node
	 */
	public function testBondNode() {
		$packet0 = '00.00.00.04.ff.ff.00.00';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet0, 12000)->andReturn([true]);
		Assert::same([true], $this->iqrfNetManager->bondNode());
		$packet1 = '00.00.00.04.ff.ff.0f.00';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet1, 12000)->andReturn([true]);
		Assert::same([true], $this->iqrfNetManager->bondNode('f'));
	}

	/**
	 * Test function to discovery IQMESH Network
	 */
	public function testDiscovery() {
		$packet0 = '00.00.00.07.ff.ff.00.00';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet0, 0)->andReturn([true]);
		Assert::same([true], $this->iqrfNetManager->discovery());
		$packet1 = '00.00.00.07.ff.ff.06.ef';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet1, 0)->andReturn([true]);
		Assert::same([true], $this->iqrfNetManager->discovery(6, 'ef'));
	}

	/**
	 * Test function to rebond node
	 */
	public function testRebondNode() {
		$packet = '00.00.00.06.ff.ff.10';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet)->andReturn([true]);
		Assert::same([true], $this->iqrfNetManager->rebondNode('10'));
	}

	/**
	 * Test function to removenode
	 */
	public function testRemoveNode() {
		$packet = '00.00.00.05.ff.ff.10';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet)->andReturn([true]);
		Assert::same([true], $this->iqrfNetManager->removeNode('10'));
	}

	/**
	 * Test function to set IQMESH Security (Access Password and User Key)
	 */
	public function testSetSecurity() {
		$packets = [
			'00.00.02.06.ff.ff.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.',
			'00.00.02.06.ff.ff.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.de.ad.',
			'00.00.02.06.ff.ff.00.00.00.00.00.00.00.00.00.00.00.00.00.44.45.41.44.',
			'00.00.02.06.ff.ff.01.00.00.00.00.00.00.00.00.00.00.00.00.00.00.de.ad.',
			'00.00.02.06.ff.ff.01.00.00.00.00.00.00.00.00.00.00.00.00.44.45.41.44.',
		];
		foreach ($packets as $id => $packet) {
			$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet)->andReturn([$id]);
			$data = $id === 0 ? '' : 'DEAD';
			$format = $id % 2 ? IqrfNetManager::DATA_FORMAT_HEX : IqrfNetManager::DATA_FORMAT_ASCII;
			$type = $id > 2 ? IqrfNetManager::SECURITY_USER_KEY : IqrfNetManager::SECURITY_ACCESS_PASSOWRD;
			Assert::same([$id], $this->iqrfNetManager->setSecurity($data, $format, $type));
		}
		Assert::exception(function() {
			$this->iqrfNetManager->setSecurity('DEAD', 'DEAD');
		}, UnsupportedInputFormatException::class);
		Assert::exception(function() {
			$this->iqrfNetManager->setSecurity('DEAD', 'DEAD', 'userKey');
		}, UnsupportedInputFormatException::class);
		Assert::exception(function() {
			$this->iqrfNetManager->setSecurity('DEAD', 'ASCII', 'fooBar');
		}, UnsupportedSecurityTypeException::class);
	}

	/**
	 * Test function to read HWP configuration
	 */
	public function testReadHwpConfiguration() {
		$packet = '00.00.02.02.ff.ff.';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet)->andReturn(['response']);
		$this->iqrfAppManager->shouldReceive('parseResponse')->with(['response'])->andReturn([true]);
		Assert::same([true], $this->iqrfNetManager->readHwpConfiguration());
	}

	/**
	 * Test function to write HWP configuration byte
	 */
	public function testWriteHwpConfigurationByte() {
		$packet = '00.00.02.09.ff.ff.06.34.ff';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet)->andReturn([true]);
		Assert::same([true], $this->iqrfNetManager->writeHwpConfigurationByte('06', '34'));
	}

	/**
	 * Test function to set RF channel
	 */
	public function testSetRfChannel() {
		$packet0 = '00.00.02.09.ff.ff.11.34.ff';
		$packet1 = '00.00.02.09.ff.ff.12.40.ff';
		$packet2 = '00.00.02.09.ff.ff.06.1a.ff';
		$packet3 = '00.00.02.09.ff.ff.07.20.ff';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet0)->andReturn([0]);
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet1)->andReturn([1]);
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet2)->andReturn([2]);
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet3)->andReturn([3]);
		Assert::same([0], $this->iqrfNetManager->setRfChannel(52, IqrfNetManager::MAIN_RF_CHANNEL_A));
		Assert::same([1], $this->iqrfNetManager->setRfChannel(64, IqrfNetManager::MAIN_RF_CHANNEL_B));
		Assert::same([2], $this->iqrfNetManager->setRfChannel(26, IqrfNetManager::ALTERNATIVE_RF_CHANNEL_A));
		Assert::same([3], $this->iqrfNetManager->setRfChannel(32, IqrfNetManager::ALTERNATIVE_RF_CHANNEL_B));
		Assert::exception(function() {
			$this->iqrfNetManager->setRfChannel(52, 'test');
		}, InvalidRfChannelTypeException::class);
	}

	/**
	 * Test function to set RF LP timeout
	 */
	public function testSetRfLpTimeout() {
		$packet0 = '00.00.02.09.ff.ff.0a.01.ff';
		$packet1 = '00.00.02.09.ff.ff.0a.ff.ff';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet0)->andReturn([0]);
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet1)->andReturn([1]);
		Assert::same([0], $this->iqrfNetManager->setRfLpTimeout(1));
		Assert::same([1], $this->iqrfNetManager->setRfLpTimeout(255));
		Assert::exception(function() {
			$this->iqrfNetManager->setRfLpTimeout(0);
		}, InvalidRfLpTimeoutException::class);
		Assert::exception(function() {
			$this->iqrfNetManager->setRfLpTimeout(256);
		}, InvalidRfLpTimeoutException::class);
	}

	/**
	 * Test function to set RF output power
	 */
	public function testSetRfOutputPower() {
		$packet0 = '00.00.02.09.ff.ff.08.00.ff';
		$packet1 = '00.00.02.09.ff.ff.08.07.ff';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet0)->andReturn([0]);
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet1)->andReturn([1]);
		Assert::same([0], $this->iqrfNetManager->setRfOutputPower(0));
		Assert::same([1], $this->iqrfNetManager->setRfOutputPower(7));
		Assert::exception(function() {
			$this->iqrfNetManager->setRfOutputPower(-1);
		}, InvalidRfOutputPowerException::class);
		Assert::exception(function() {
			$this->iqrfNetManager->setRfOutputPower(8);
		}, InvalidRfOutputPowerException::class);
	}

	/**
	 * Test function to set RF signal filter
	 */
	public function testSetRfSignalFilter() {
		$packet0 = '00.00.02.09.ff.ff.09.00.ff';
		$packet1 = '00.00.02.09.ff.ff.09.40.ff';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet0)->andReturn([0]);
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet1)->andReturn([1]);
		Assert::same([0], $this->iqrfNetManager->setRfSignalFilter(0));
		Assert::same([1], $this->iqrfNetManager->setRfSignalFilter(64));
		Assert::exception(function() {
			$this->iqrfNetManager->setRfSignalFilter(-1);
		}, InvalidRfSignalFilterException::class);
		Assert::exception(function() {
			$this->iqrfNetManager->setRfSignalFilter(65);
		}, InvalidRfSignalFilterException::class);
	}

}

$test = new IqrfNetManagerTest($container);
$test->run();
