<?php
/**
 * TEST: App\IqrfNetModule\Models\OsManager
 * @covers App\IqrfNetModule\Models\OsManager
 * @phpVersion >= 7.2
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Models;

use App\IqrfNetModule\Models\OsManager;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Test for DPA OS peripheral manager
 */
final class OsManagerTest extends WebSocketTestCase {

	/**
	 * Network device address
	 */
	private const ADDRESS = 1;

	/**
	 * @var OsManager DPA OS peripheral manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new OsManager($this->request, $this->wsClient);
	}

	/**
	 * Tests the function to read IQRF OS information
	 */
	public function testRun(): void {
		$request = [
			'mType' => 'iqrfEmbedOs_Read',
			'data' => [
				'req' => [
					'nAdr' => self::ADDRESS,
					'param' => (object) [],
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->read(self::ADDRESS);
		});
	}

}

$test = new OsManagerTest();
$test->run();
