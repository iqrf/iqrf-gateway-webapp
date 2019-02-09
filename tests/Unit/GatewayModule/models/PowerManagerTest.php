<?php
/**
 * TEST: App\GatewayModule\Models\PowerManager
 * @covers App\GatewayModule\Models\PowerManager
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\GatewayModule\Models;

use App\GatewayModule\Models\PowerManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for tool for powering off and rebooting IQRF Gateway
 */
class PowerManagerTest extends CommandTestCase {

	/**
	 * @var PowerManager Tool for powering off and rebooting IQRF Gateway
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new PowerManager($this->commandManager);
	}

	/**
	 * Tests the function to power off iQRF Gateway
	 */
	public function testPowerOff(): void {
		$this->receiveCommand('poweroff', true, '');
		Assert::noError([$this->manager, 'powerOff']);
	}

	/**
	 * Tests the function to reboot iQRF Gateway
	 */
	public function testReboot(): void {
		$this->receiveCommand('reboot', true, '');
		Assert::noError([$this->manager, 'reboot']);
	}

}

$test = new PowerManagerTest();
$test->run();
