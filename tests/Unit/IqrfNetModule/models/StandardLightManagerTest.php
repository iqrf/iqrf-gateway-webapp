<?php
/**
 * TEST: App\IqrfNetModule\Models\StandardLightManager
 * @covers App\IqrfNetModule\Models\StandardLightManager
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\GatewayModule\Models;

use App\IqrfNetModule\Entities\StandardLight;
use App\IqrfNetModule\Models\StandardLightManager;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Test for IQRF Standard light manager
 */
class StandardLightManagerTest extends WebSocketTestCase {

	/**
	 * @var int IQRF Standard light network device address
	 */
	private $address = 3;

	/**
	 * @var StandardLightManager IQRF Standard light manager
	 */
	private $manager;

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new StandardLightManager($this->request, $this->wsClient);
	}

	/**
	 * Tests function to enumerate IQRF Standard light device
	 */
	public function testEnumerate(): void {
		$request = [
			'mType' => 'iqrfLight_Enumerate',
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
	 * Test the function to set power of IQRF Standard light
	 */
	public function testSetPower(): void {
		$request = [
			'mType' => 'iqrfLight_SetPower',
			'data' => [
				'req' => [
					'nAdr' => $this->address,
					'param' => [
						'lights' => [
							[
								'index' => 0,
								'power' => 50,
							],
						],
					],
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$light = new StandardLight(0, 50);
			$this->manager->setPower($this->address, [$light]);
		});
	}

	/**
	 * Test the function to get power of IQRF Standard light
	 */
	public function testGetPower(): void {
		$request = [
			'mType' => 'iqrfLight_SetPower',
			'data' => [
				'req' => [
					'nAdr' => $this->address,
					'param' => [
						'lights' => [
							[
								'index' => 0,
								'power' => 127,
							],
						],
					],
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$light = new StandardLight(0, 50);
			$this->manager->getPower($this->address, [$light]);
		});
	}

	/**
	 * Test the function to increment power of IQRF Standard light
	 */
	public function testIncrementPower(): void {
		$request = [
			'mType' => 'iqrfLight_IncrementPower',
			'data' => [
				'req' => [
					'nAdr' => $this->address,
					'param' => [
						'lights' => [
							[
								'index' => 0,
								'power' => 50,
							],
						],
					],
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$light = new StandardLight(0, 50);
			$this->manager->incrementPower($this->address, [$light]);
		});
	}

	/**
	 * Test the function to decrement power of IQRF Standard light
	 */
	public function testDecrementPower(): void {
		$request = [
			'mType' => 'iqrfLight_DecrementPower',
			'data' => [
				'req' => [
					'nAdr' => $this->address,
					'param' => [
						'lights' => [
							[
								'index' => 0,
								'power' => 50,
							],
						],
					],
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$light = new StandardLight(0, 50);
			$this->manager->decrementPower($this->address, [$light]);
		});
	}

}

$test = new StandardLightManagerTest();
$test->run();
