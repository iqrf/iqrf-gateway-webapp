<?php
/**
 * TEST: App\IqrfNetModule\Models\StandardSensorManager
 * @covers App\IqrfNetModule\Models\StandardSensorManager
 * @phpVersion >= 7.1
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
class StandardSensorManagerTest extends WebSocketTestCase {

	/**
	 * @var int IQRF Standard sensor network device address
	 */
	private $address = 1;

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
					'nAdr' => $this->address,
					'param' => (object) [],
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->enumerate($this->address);
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
					'nAdr' => $this->address,
					'param' => ['sensorIndexes' => -1],
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->readAll($this->address);
		});
	}

}

$test = new StandardSensorManagerTest();
$test->run();
