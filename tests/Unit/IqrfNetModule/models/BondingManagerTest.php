<?php
/**
 * TEST: App\IqrfNetModule\Models\BondingManager
 * @covers App\IqrfNetModule\Models\BondingManager
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Models;

use App\IqrfNetModule\Models\BondingManager;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Test for IQMESH Bonding manager
 */
class BondingManagerTest extends WebSocketTestCase {

	/**
	 * @var int Network device address
	 */
	private $address = 1;

	/**
	 * @var BondingManager IQMESH Bonding manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new BondingManager($this->request, $this->wsClient);
	}

	/**
	 * Tests the function to bond a node locally
	 */
	public function testBondLocal(): void {
		$request = [
			'mType' => 'iqmeshNetwork_BondNodeLocal',
			'data' => [
				'repeat' => 2,
				'req' => [
					'deviceAddr' => $this->address,
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->bondLocal($this->address);
		});
	}

	/**
	 * Tests the function to bond a node via IQRF Smart Connect
	 */
	public function testBondSmartConnect(): void {
		$request = [
			'mType' => 'iqmeshNetwork_SmartConnect',
			'data' => [
				'repeat' => 2,
				'req' => [
					'deviceAddr' => $this->address,
					'smartConnectCode' => 'smartConnectCode',
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->bondSmartConnect($this->address, 'smartConnectCode');
		});
	}

	/**
	 * Tests the function to clear all bonds
	 */
	public function testClearAll(): void {
		$request = [
			'mType' => 'iqmeshNetwork_RemoveBond',
			'data' => [
				'repeat' => 2,
				'req' => [
					'deviceAddr' => 255,
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, [$this->manager, 'clearAll']);
	}

	/**
	 * Tests the function to remove a bond
	 */
	public function testRemove(): void {
		$request = [
			'mType' => 'iqmeshNetwork_RemoveBond',
			'data' => [
				'repeat' => 2,
				'req' => [
					'deviceAddr' => $this->address,
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->remove($this->address);
		});
	}

}

$test = new BondingManagerTest();
$test->run();
