<?php
/**
 * TEST: App\GatewayModule\Models\PowerManager
 * @covers App\GatewayModule\Models\PowerManager
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\GatewayModule\Models;

use App\CoreModule\Models\CommandManager;
use App\GatewayModule\Models\PowerManager;
use Mockery;
use Mockery\MockInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for tool for powering off and rebooting IQRF Gateway
 */
class PowerManagerTest extends TestCase {

	/**
	 * @var MockInterface Mocked command manager
	 */
	private $commandManager;

	/**
	 * @var PowerManager Tool for powering off and rebooting IQRF Gateway
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->commandManager = Mockery::mock(CommandManager::class);
		$this->manager = new PowerManager($this->commandManager);
	}

	/**
	 * Cleanups the test environment
	 */
	protected function tearDown(): void {
		Mockery::close();
	}

	/**
	 * Tests the function to power off iQRF Gateway
	 */
	public function testPowerOff(): void {
		$this->commandManager->shouldReceive('send')
			->with('poweroff', true);
		Assert::noError([$this->manager, 'powerOff']);
	}

	/**
	 * Tests the function to reboot iQRF Gateway
	 */
	public function testReboot(): void {
		$this->commandManager->shouldReceive('send')
			->with('reboot', true);
		Assert::noError([$this->manager, 'reboot']);
	}

}

$test = new PowerManagerTest();
$test->run();
