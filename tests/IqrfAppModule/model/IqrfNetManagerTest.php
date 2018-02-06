<?php

/**
 * TEST: App\IqrfAppModule\Model\IqrfNetManager
 * @covers App\IqrfAppModule\Model\IqrfNetManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types=1);

namespace Test\IqrfAppModule\Model;

use App\IqrfAppModule\Model\IqrfAppManager;
use App\IqrfAppModule\Model\IqrfNetManager;
use App\IqrfAppModule\Model\UnsupportedInputFormatException;
use App\IqrfAppModule\Model\UnsupportedSecurityTypeException;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class IqrfNetManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var IqrfAppManager iqrfapp manager
	 */
	private $iqrfAppManager;

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
	}

	/**
	 * @test
	 * Test function to clear all bonds
	 */
	public function testClearAllBonds() {
		$packet = '00.00.00.03.ff.ff';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet)->andReturn([true]);
		$iqrfNetManager = new IqrfNetManager($this->iqrfAppManager);
		Assert::same([true], $iqrfNetManager->clearAllBonds());
	}

	/**
	 * @test
	 * Test function to bond new node
	 */
	public function testBondNode() {
		$packet0 = '00.00.00.04.ff.ff.00.00';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet0, 12000)->andReturn([true]);
		$packet1 = '00.00.00.04.ff.ff.0f.00';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet1, 12000)->andReturn([true]);
		$iqrfNetManager = new IqrfNetManager($this->iqrfAppManager);
		Assert::same([true], $iqrfNetManager->bondNode());
		Assert::same([true], $iqrfNetManager->bondNode('f'));
	}

	/**
	 * @test
	 * Test function to discovery IQMESH Network
	 */
	public function testDiscovery() {
		$packet0 = '00.00.00.07.ff.ff.00.00';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet0, 0)->andReturn([true]);
		$packet1 = '00.00.00.07.ff.ff.06.ef';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet1, 0)->andReturn([true]);
		$iqrfNetManager = new IqrfNetManager($this->iqrfAppManager);
		Assert::same([true], $iqrfNetManager->discovery());
		Assert::same([true], $iqrfNetManager->discovery(6, 'ef'));
	}

	/**
	 * @test
	 * Test function to rebond node
	 */
	public function testRebondNode() {
		$packet = '00.00.00.06.ff.ff.10';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet)->andReturn([true]);
		$iqrfNetManager = new IqrfNetManager($this->iqrfAppManager);
		Assert::same([true], $iqrfNetManager->rebondNode('10'));
	}

	/**
	 * @test
	 * Test function to removenode
	 */
	public function testRemoveNode() {
		$packet = '00.00.00.05.ff.ff.10';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet)->andReturn([true]);
		$iqrfNetManager = new IqrfNetManager($this->iqrfAppManager);
		Assert::same([true], $iqrfNetManager->removeNode('10'));
	}

	/**
	 * @test
	 * Test function to set IQMESH Security (Access Password and User Key)
	 */
	public function testSetSecurity() {
		$packet0 = '00.00.02.06.ff.ff.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.';
		$packet1 = '00.00.02.06.ff.ff.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.de.ad.';
		$packet2 = '00.00.02.06.ff.ff.00.00.00.00.00.00.00.00.00.00.00.00.00.44.45.41.44.';
		$packet3 = '00.00.02.06.ff.ff.01.00.00.00.00.00.00.00.00.00.00.00.00.00.00.de.ad.';
		$packet4 = '00.00.02.06.ff.ff.01.00.00.00.00.00.00.00.00.00.00.00.00.44.45.41.44.';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet0)->andReturn([0]);
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet1)->andReturn([1]);
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet2)->andReturn([2]);
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet3)->andReturn([3]);
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet4)->andReturn([4]);
		$iqrfNetManager = new IqrfNetManager($this->iqrfAppManager);
		Assert::same([0], $iqrfNetManager->setSecurity());
		Assert::same([1], $iqrfNetManager->setSecurity('DEAD', 'HEX'));
		Assert::same([2], $iqrfNetManager->setSecurity('DEAD', 'ASCII'));
		Assert::same([3], $iqrfNetManager->setSecurity('DEAD', 'HEX', 'userKey'));
		Assert::same([4], $iqrfNetManager->setSecurity('DEAD', 'ASCII', 'userKey'));
		Assert::exception(function() use ($iqrfNetManager) {
			$iqrfNetManager->setSecurity('DEAD', 'DEAD');
		}, UnsupportedInputFormatException::class);
		Assert::exception(function() use ($iqrfNetManager) {
			$iqrfNetManager->setSecurity('DEAD', 'DEAD', 'userKey');
		}, UnsupportedInputFormatException::class);
		Assert::exception(function() use ($iqrfNetManager) {
			$iqrfNetManager->setSecurity('DEAD', 'ASCII', 'fooBar');
		}, UnsupportedSecurityTypeException::class);
	}

	/**
	 * @test
	 * Test function to read HWP configuration
	 */
	public function testReadHwpConfiguration() {
		$packet = '00.00.02.02.ff.ff.';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet)->andReturn([true]);
		$iqrfNetManager = new IqrfNetManager($this->iqrfAppManager);
		Assert::same([true], $iqrfNetManager->readHwpConfiguration());
	}

	/**
	 * @test
	 * Test function to write HWP configuration byte
	 */
	public function testWriteHwpConfigurationByte() {
		$packet = '00.00.02.09.ff.ff.06.34.ff';
		$this->iqrfAppManager->shouldReceive('sendRaw')->with($packet)->andReturn([true]);
		$iqrfNetManager = new IqrfNetManager($this->iqrfAppManager);
		Assert::same([true], $iqrfNetManager->writeHwpConfigurationByte('06', '34'));
	}

}

$test = new IqrfNetManagerTest($container);
$test->run();
