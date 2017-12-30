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

}

$test = new IqrfNetManagerTest($container);
$test->run();
