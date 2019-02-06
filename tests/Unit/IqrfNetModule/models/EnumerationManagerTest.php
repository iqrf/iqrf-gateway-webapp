<?php
/**
 * TEST: App\IqrfNetModule\Models\EnumerationManager
 * @covers App\IqrfNetModule\Models\EnumerationManager
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\GatewayModule\Models;

use App\IqrfNetModule\Models\EnumerationManager;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Test for IQMESH Enumeration manager
 */
class EnumerationManagerTest extends WebSocketTestCase {

	/**
	 * @var int Network device address
	 */
	private $address = 1;

	/**
	 * @var EnumerationManager IQMESH Enumeration manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new EnumerationManager($this->request, $this->wsClient);
	}

	/**
	 * Tests the function to run IQMESH Enumeration process
	 */
	public function testRun(): void {
		$request = [
			'mType' => 'iqmeshNetwork_EnumerateDevice',
			'data' => [
				'repeat' => 2,
				'req' => [
					'deviceAddr' => $this->address,
					'morePeripheralsInfo' => true,
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->device($this->address);
		});
	}

}

$test = new EnumerationManagerTest();
$test->run();
