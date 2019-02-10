<?php

/**
 * TEST: App\GatewayModule\Models\DeviceTreeBoardInfoManager
 * @covers App\GatewayModule\Models\DeviceTreeBoardInfoManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Test\Unit\GatewayModule\Models;

use App\GatewayModule\Models\DeviceTreeBoardInfoManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Device tree board manager
 */
class DeviceTreeBoardInfoManagerTest extends CommandTestCase {

	/**
	 * @var DeviceTreeBoardInfoManager Device tree board manager
	 */
	private $manager;

	/**
	 * @var string Command
	 */
	private $command = 'cat /proc/device-tree/model';

	/**
	 * Tests the function to get board's name from DMI (success)
	 */
	public function testGetNameSuccess(): void {
		$expected = 'Raspberry Pi 2 Models B Rev 1.1';
		$this->receiveCommand($this->command, true, $expected);
		Assert::same($expected, $this->manager->getName());
	}

	/**
	 * Tests the function to get board's name from DMI (fail)
	 */
	public function testGetNameFail(): void {
		$this->receiveCommand($this->command, true);
		Assert::null($this->manager->getName());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new DeviceTreeBoardInfoManager($this->commandManager);
	}

}

$test = new DeviceTreeBoardInfoManagerTest();
$test->run();
