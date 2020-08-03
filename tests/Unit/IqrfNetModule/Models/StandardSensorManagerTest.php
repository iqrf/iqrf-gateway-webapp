<?php
/**
 * TEST: App\IqrfNetModule\Models\StandardSensorManager
 * @covers App\IqrfNetModule\Models\StandardSensorManager
 * @phpVersion >= 7.2
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Models;

use App\IqrfNetModule\Models\StandardSensorManager;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Test for IQRF Standard sensor manager
 */
final class StandardSensorManagerTest extends WebSocketTestCase {

	/**
	 * IQRF Standard sensor network device address
	 */
	private const ADDRESS = 1;

	/**
	 * @var StandardSensorManager IQRF Standard sensor manager
	 */
	private $manager;

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new StandardSensorManager($this->request, $this->wsClient);
	}

	/**
	 * Tests function to enumerate IQRF Standard light device
	 */
	public function testEnumerate(): void {
		$request = [
			'mType' => 'iqrfSensor_Enumerate',
			'data' => [
				'req' => [
					'nAdr' => self::ADDRESS,
					'param' => (object) [],
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->enumerate(self::ADDRESS);
		});
	}

	/**
	 * Tests the function to read all sensors with types
	 */
	public function testReadAll(): void {
		$request = [
			'mType' => 'iqrfSensor_ReadSensorsWithTypes',
			'data' => [
				'req' => [
					'nAdr' => self::ADDRESS,
					'param' => ['sensorIndexes' => -1],
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->readAll(self::ADDRESS);
		});
	}

}

$test = new StandardSensorManagerTest();
$test->run();
