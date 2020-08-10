<?php

/**
 * TEST: App\GatewayModule\Models\DeviceTreeBoardManager
 * @covers App\GatewayModule\Models\DeviceTreeBoardManager
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\GatewayModule\Models\BoardManagers;

use App\GatewayModule\Models\BoardManagers\DeviceTreeBoardManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for Device tree board manager
 */
final class DeviceTreeBoardManagerTest extends CommandTestCase {

	/**
	 * @var DeviceTreeBoardManager Device tree board manager
	 */
	private $manager;

	/**
	 * Executed command
	 */
	private const COMMAND = 'cat /proc/device-tree/model';

	/**
	 * Tests the function to get board's name from DMI (success)
	 */
	public function testGetNameSuccess(): void {
		$expected = 'Raspberry Pi 2 Models B Rev 1.1';
		$this->receiveCommand(self::COMMAND, true, $expected);
		Assert::same($expected, $this->manager->getName());
	}

	/**
	 * Tests the function to get board's name from DMI (fail)
	 */
	public function testGetNameFail(): void {
		$this->receiveCommand(self::COMMAND, true);
		Assert::null($this->manager->getName());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new DeviceTreeBoardManager($this->commandManager);
	}

}

$test = new DeviceTreeBoardManagerTest();
$test->run();
