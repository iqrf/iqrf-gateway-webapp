<?php
/**
 * TEST: App\IqrfNetModule\Models\TrConfigManager
 * @covers App\IqrfNetModule\Models\TrConfigManager
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Models;

use App\IqrfNetModule\Models\TrConfigManager;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Test for IQMESH TrConfig manager
 */
class TrConfigManagerTest extends WebSocketTestCase {

	/**
	 * @var int Network device address
	 */
	private $address = 1;

	/**
	 * @var TrConfigManager IQRF TR configuration manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new TrConfigManager($this->request, $this->wsClient);
	}

	/**
	 * Tests the function to read TR configuration
	 */
	public function testRead(): void {
		$request = [
			'mType' => 'iqmeshNetwork_ReadTrConf',
			'data' => [
				'repeat' => 2,
				'req' => [
					'deviceAddr' => $this->address,
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->read($this->address);
		});
	}

	/**
	 * Tests the function to write TR configuration
	 */
	public function testWrite(): void {
		$request = [
			'mType' => 'iqmeshNetwork_WriteTrConf',
			'data' => [
				'repeat' => 2,
				'req' => [
					'deviceAddr' => $this->address,
					'rfChannelA' => 48,
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$configuration = ['rfChannelA' => 48];
			$this->manager->write($this->address, $configuration);
		});
	}

}

$test = new TrConfigManagerTest();
$test->run();
