<?php
/**
 * TEST: App\IqrfNetModule\Models\EnumerationManager
 * @covers App\IqrfNetModule\Models\EnumerationManager
 * @phpVersion >= 7.2
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Models;

use App\IqrfNetModule\Models\EnumerationManager;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Test for IQMESH Enumeration manager
 */
final class EnumerationManagerTest extends WebSocketTestCase {

	/**
	 * Network device address
	 */
	private const ADDRESS = 1;

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
					'deviceAddr' => self::ADDRESS,
					'morePeripheralsInfo' => true,
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->device(self::ADDRESS);
		});
	}

}

$test = new EnumerationManagerTest();
$test->run();
