<?php
/**
 * TEST: App\NetworkModule\Models\InterfaceManager
 * @covers App\NetworkModule\Models\InterfaceManager
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Models;

use App\NetworkModule\Entities\InterfaceStatus;
use App\NetworkModule\Enums\InterfaceStates;
use App\NetworkModule\Enums\InterfaceTypes;
use App\NetworkModule\Models\InterfaceManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for network interface manager
 */
class InterfaceManagerTest extends CommandTestCase {

	/**
	 * @var InterfaceManager Network interface manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new InterfaceManager($this->commandManager);
	}

	/**
	 * Tests the function to connect the network interface
	 */
	public function testConnect(): void {
		$this->receiveCommand('nmcli -t device connect eth0', true);
		Assert::noError(function (): void {
			$this->manager->connect('eth0');
		});
	}

	/**
	 * Tests the function to disconnect the network interface
	 */
	public function testDisconnect(): void {
		$this->receiveCommand('nmcli -t device disconnect eth0', true);
		Assert::noError(function (): void {
			$this->manager->disconnect('eth0');
		});
	}

	/**
	 * Tests the function to list network interfaces
	 */
	public function testList(): void {
		$output = 'eth0:ethernet:connected:eth0' . PHP_EOL
			. 'wlan0:wifi:disconnected:' . PHP_EOL
			. 'lo:loopback:unmanaged:' . PHP_EOL;
		$this->receiveCommand('nmcli -t device status', true, $output);
		$expected = [
			new InterfaceStatus('eth0', InterfaceTypes::ETHERNET(), InterfaceStates::CONNECTED(), 'eth0'),
			new InterfaceStatus('wlan0', InterfaceTypes::WIFI(), InterfaceStates::DISCONNECTED(), ''),
			new InterfaceStatus('lo', InterfaceTypes::LOOPBACK(), InterfaceStates::UNMANAGED(), ''),
		];
		Assert::equal($expected, $this->manager->list());
	}

}

$test = new InterfaceManagerTest();
$test->run();
