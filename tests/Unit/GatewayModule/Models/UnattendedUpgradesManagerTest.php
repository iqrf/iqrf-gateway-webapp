<?php
/**
 * TEST: App\GatewayModule\Models\UnattendedUpgradesManager
 * @covers App\GatewayModule\Models\UnattendedUpgradesManager
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\GatewayModule\Models;

use App\GatewayModule\Models\UnattendedUpgradesManager;
use App\ServiceModule\Enums\ServiceStates;
use App\ServiceModule\Models\SystemDManager;
use Mockery;
use Mockery\MockInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Unattended upgrades manager
 */
class UnattendedUpgradesManagerTest extends TestCase {

	/**
	 * @var UnattendedUpgradesManager Unattended upgrades manager
	 */
	private $manager;

	/**
	 * @var SystemDManager|MockInterface SystemD service manager
	 */
	private $serviceManager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->serviceManager = Mockery::mock(SystemDManager::class);
		$this->manager = new UnattendedUpgradesManager($this->serviceManager);
	}

	/**
	 * Tests the function to disable Unattended upgrades service
	 */
	public function testDisableService(): void {
		$this->serviceManager->shouldReceive('disable');
		Assert::noError(function (): void {
			$this->manager->disableService();
		});
	}

	/**
	 * Tests the function to enable Unattended upgrades service
	 */
	public function testEnableService(): void {
		$this->serviceManager->shouldReceive('enable');
		Assert::noError(function (): void {
			$this->manager->enableService();
		});
	}

	/**
	 * Tests the function to get Unattended service status
	 */
	public function testGetServiceStatus(): void {
		$expected = ServiceStates::ENABLED();
		$this->serviceManager->shouldReceive('isEnabled')
			->andReturn($expected);
		Assert::equal($expected, $this->manager->getServiceStatus());
	}

}

$test = new UnattendedUpgradesManagerTest();
$test->run();
