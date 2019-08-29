<?php
/**
 * TEST: App\IqrfNetModule\Models\StandardBinaryOutputManager
 * @covers App\IqrfNetModule\Models\StandardBinaryOutputManager
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Models;

use App\IqrfNetModule\Entities\StandardBinaryOutput;
use App\IqrfNetModule\Models\StandardBinaryOutputManager;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Test for IQRF Standard binary output manager
 */
class StandardBinaryOutputManagerTest extends WebSocketTestCase {

	/**
	 * @var int IQRF Standard binary output network device address
	 */
	private $address = 2;

	/**
	 * @var StandardBinaryOutputManager IQRF Standard binary output manager
	 */
	private $manager;

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new StandardBinaryOutputManager($this->request, $this->wsClient);
	}

	/**
	 * Tests the function to enumerate IQRF Standard binary output device
	 */
	public function testEnumerate(): void {
		$request = [
			'mType' => 'iqrfBinaryoutput_Enumerate',
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
	 * Tests the function to gets state of binary outputs
	 */
	public function testGetOutputs(): void {
		$request = [
			'mType' => 'iqrfBinaryoutput_SetOutput',
			'data' => [
				'req' => [
					'nAdr' => $this->address,
					'param' => [
						'binOuts' => [],
					],
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->getOutputs($this->address);
		});
	}

	/**
	 * Tests the function to sets state of binary outputs
	 */
	public function testSetOutputs(): void {
		$request = [
			'mType' => 'iqrfBinaryoutput_SetOutput',
			'data' => [
				'req' => [
					'nAdr' => $this->address,
					'param' => [
						'binOuts' => [
							[
								'index' => 0,
								'state' => true,
							],
						],
					],
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$output = new StandardBinaryOutput(0, true);
			$this->manager->setOutputs($this->address, [$output]);
		});
	}

}

$test = new StandardBinaryOutputManagerTest();
$test->run();
