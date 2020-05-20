<?php
/**
 * TEST: App\IqrfNetModule\Models\DiscoveryManager
 * @covers App\IqrfNetModule\Models\DiscoveryManager
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Models;

use App\IqrfNetModule\Models\DiscoveryManager;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Test for IQMESH Discovery manager
 */
class DiscoveryManagerTest extends WebSocketTestCase {

	/**
	 * @var DiscoveryManager IQMESH Discovery manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new DiscoveryManager($this->request, $this->wsClient);
	}

	/**
	 * Tests the function to run IQMESH Discovery process
	 */
	public function testRun(): void {
		$request = [
			'mType' => 'iqrfEmbedCoordinator_Discovery',
			'data' => [
				'req' => [
					'nAdr' => 0,
					'param' => [
						'txPower' => 6,
						'maxAddr' => 0,
					],
				],
			],
		];
		$this->assertRequest($request, function (): array {
			return $this->manager->run();
		});
	}

}

$test = new DiscoveryManagerTest();
$test->run();
