<?php
/**
 * TEST: App\IqrfNetModule\Models\DevicesManager
 * @covers App\IqrfNetModule\Models\DevicesManager
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Models;

use App\IqrfNetModule\Entities\DeviceStatus;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Models\DevicesManager;
use Mockery;
use Mockery\Exception;
use Mockery\Mock;
use Tester\Assert;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Test for showing devices in IQMESH Network
 */
class DevicesManagerTest extends WebSocketTestCase {

	/**
	 * @var DevicesManager|Mock IQMESH Devices manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$dependencies = [$this->request, $this->wsClient];
		$this->manager = Mockery::mock(DevicesManager::class, $dependencies)->makePartial();
	}

	/**
	 * Tests the function to get bonded devices
	 */
	public function testGetBonded(): void {
		$request = [
			'mType' => 'iqrfEmbedCoordinator_BondedDevices',
			'data' => [
				'req' => [
					'nAdr' => 0,
					'param' => (object) [],
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, [$this->manager, 'getBonded']);
	}

	/**
	 * Tests the function to get discovered devices
	 */
	public function testGetDiscovered(): void {
		$request = [
			'mType' => 'iqrfEmbedCoordinator_DiscoveredDevices',
			'data' => [
				'req' => [
					'nAdr' => 0,
					'param' => (object) [],
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, [$this->manager, 'getDiscovered']);
	}

	/**
	 * Tests the function to get table of devices in IQMESH Network (an error occurred)
	 */
	public function testGetTableFail(): void {
		$this->manager->shouldReceive('getBonded')
			->andThrow(DpaErrorException::class);
		$this->manager->shouldReceive('getDiscovered')
			->andThrow(DpaErrorException::class);
		$table = [
			[1, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
		];
		$expected = [];
		foreach ($table as $x => $row) {
			foreach ($row as $y => $item) {
				$status = new DeviceStatus($x * 10 + $y);
				$status->setType($item);
				$expected[$x][$y] = $status;
			}
		}
		Assert::equal($expected, $this->manager->getTable(0));
	}

	/**
	 * Tests the function to get table of devices in IQMESH Network (success)
	 */
	public function testGetTableSuccess(): void {
		$bonded = $this->readJsonResponse('iqrfEmbedCoordinator_BondedDevices')['response']['data']['rsp']['result']['bondedDevices'];
		$discovered = $this->readJsonResponse('iqrfEmbedCoordinator_DiscoveredDevices')['response']['data']['rsp']['result']['discoveredDevices'];
		$this->manager->shouldReceive('getBonded')->andReturn($bonded);
		$this->manager->shouldReceive('getDiscovered')->andReturn($discovered);
		$table = [
			[1, 2, 2, 2, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
		];
		$expected = [];
		foreach ($table as $x => $row) {
			foreach ($row as $y => $item) {
				$status = new DeviceStatus($x * 10 + $y);
				$status->setType($item);
				$expected[$x][$y] = $status;
			}
		}
		Assert::equal($expected, $this->manager->getTable(10));
	}

	/**
	 * Tests the function to ping devices
	 */
	public function testPing(): void {
		$request = [
			'mType' => 'iqrfEmbedFrc_Send',
			'data' => [
				'req' => [
					'nAdr' => 0,
					'param' => [
						'frcCommand' => 0,
						'userData' => [0, 0],
					],
					'returnVerbose' => true,
				],
			],
		];
		$this->assertRequest($request, [$this->manager, 'ping']);
	}

}

$test = new DevicesManagerTest();
$test->run();
