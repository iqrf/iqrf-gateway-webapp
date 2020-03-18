<?php
/**
 * TEST: App\GatewayModule\Models\SshManager
 * @covers App\GatewayModule\Models\SshManager
 * @phpVersion >= 7.2
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\GatewayModule\Models;

use App\GatewayModule\Models\SshManager;
use App\ServiceModule\Enums\ServiceStates;
use App\ServiceModule\Models\SystemDManager;
use Mockery;
use Mockery\MockInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for SSH daemon manager
 */
class SshManagerTest extends TestCase {

	/**
	 * @var SshManager SSH daemon manager
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
		$this->manager = new SshManager($this->serviceManager);
	}

	/**
	 * Tests the function to disable SSH daemon service
	 */
	public function testDisableService(): void {
		$this->serviceManager->shouldReceive('disable');
		Assert::noError([$this->manager, 'disableService']);
	}

	/**
	 * Tests the function to enable SSH daemon service
	 */
	public function testEnableService(): void {
		$this->serviceManager->shouldReceive('enable');
		Assert::noError([$this->manager, 'enableService']);
	}

	/**
	 * Tests the function to get SSH daemon service status
	 */
	public function testGetServiceStatus(): void {
		$expected = ServiceStates::ENABLED();
		$this->serviceManager->shouldReceive('isEnabled')
			->andReturn($expected);
		Assert::equal($expected, $this->manager->getServiceStatus());
	}

}

$test = new SshManagerTest();
$test->run();
