<?php
/**
 * TEST: App\GatewayModule\Models\AptGetPackageManager
 * @covers App\GatewayModule\Models\AptGetPackageManager
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\GatewayModule\Models;

use App\GatewayModule\Models\AptGetPackageManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for tool for apt-get package manager
 */
class AptGetPackageManagerTest extends CommandTestCase {

	/**
	 * @var string[] Packages
	 */
	private $packages = ['iqrf-gateway-daemon', 'iqrf-gateway-webapp'];

	/**
	 * @var AptGetPackageManager Tool for updating IQRF Gateway
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->checkCommandExistence();
		$this->manager = new AptGetPackageManager($this->commandManager);
	}

	/**
	 * Tests the function to install packages
	 */
	public function testInstall(): void {
		$command = 'apt-get install -y iqrf-gateway-daemon iqrf-gateway-webapp';
		$this->receiveAsyncCommand([$this, 'callback'], $command, true);
		Assert::noError(function (): void {
			$this->manager->install([$this, 'callback'], $this->packages);
		});
	}

	/**
	 * Tests the function to list upgradable packages
	 */
	public function testListUpgradable(): void {
		$command = 'apt-get -s upgrade -V';
		$this->receiveAsyncCommand([$this, 'callback'], $command, true);
		Assert::noError(function (): void {
			$this->manager->listUpgradable([$this, 'callback']);
		});
	}

	/**
	 * Tests the function to get list of upgradable packages
	 */
	public function testGetUpgradable(): void {
		$output = 'Reading package lists...
Building dependency tree...
Reading state information...
Calculating upgrade...
The following packages will be upgraded:
   gnome-control-center (1:3.30.2-5 => 1:3.30.3-1)
   gnome-control-center-data (1:3.30.2-5 => 1:3.30.3-1)
   libkf5auth-data (5.54.0-1 => 5.54.0-2)
   libkf5auth5 (5.54.0-1 => 5.54.0-2)
   node-lru-cache (5.1.1-3 => 5.1.1-4)
5 upgraded, 0 newly installed, 0 to remove and 0 not upgraded.
Inst gnome-control-center-data [1:3.30.2-5] (1:3.30.3-1 Debian:unstable [all])
Inst gnome-control-center [1:3.30.2-5] (1:3.30.3-1 Debian:unstable [amd64])
Inst libkf5auth5 [5.54.0-1] (5.54.0-2 Debian:unstable [amd64]) []
Inst libkf5auth-data [5.54.0-1] (5.54.0-2 Debian:unstable [all])
Inst node-lru-cache [5.1.1-3] (5.1.1-4 Debian:unstable [all])
Conf gnome-control-center-data (1:3.30.3-1 Debian:unstable [all])
Conf gnome-control-center (1:3.30.3-1 Debian:unstable [amd64])
Conf libkf5auth5 (5.54.0-2 Debian:unstable [amd64])
Conf libkf5auth-data (5.54.0-2 Debian:unstable [all])
Conf node-lru-cache (5.1.1-4 Debian:unstable [all])';
		$expected = [
			[
				'id' => 0,
				'name' => 'gnome-control-center',
				'oldVersion' => '1:3.30.2-5',
				'newVersion' => '1:3.30.3-1',
			], [
				'id' => 1,
				'name' => 'gnome-control-center-data',
				'oldVersion' => '1:3.30.2-5',
				'newVersion' => '1:3.30.3-1',
			], [
				'id' => 2,
				'name' => 'libkf5auth-data',
				'oldVersion' => '5.54.0-1',
				'newVersion' => '5.54.0-2',
			], [
				'id' => 3,
				'name' => 'libkf5auth5',
				'oldVersion' => '5.54.0-1',
				'newVersion' => '5.54.0-2',
			], [
				'id' => 4,
				'name' => 'node-lru-cache',
				'oldVersion' => '5.1.1-3',
				'newVersion' => '5.1.1-4',
			],
		];
		$command = 'apt-get -s upgrade -V';
		$this->receiveCommand($command, true, $output);
		Assert::same($expected, $this->manager->getUpgradable());
	}

	/**
	 * Tests the function to remove packages
	 */
	public function testRemove(): void {
		$command = 'apt-get remove -y iqrf-gateway-daemon iqrf-gateway-webapp';
		$this->receiveAsyncCommand([$this, 'callback'], $command, true);
		Assert::noError(function (): void {
			$this->manager->remove([$this, 'callback'], $this->packages);
		});
	}

	/**
	 * Tests the function to purge packages
	 */
	public function testPurge(): void {
		$command = 'apt-get purge -y iqrf-gateway-daemon iqrf-gateway-webapp';
		$this->receiveAsyncCommand([$this, 'callback'], $command, true);
		Assert::noError(function (): void {
			$this->manager->purge([$this, 'callback'], $this->packages);
		});
	}

	/**
	 * Tests the function to update list of packages
	 */
	public function testUpdate(): void {
		$this->receiveAsyncCommand([$this, 'callback'], 'apt-get update', true);
		Assert::noError(function (): void {
			$this->manager->update([$this, 'callback']);
		});
	}

	/**
	 * Tests the function to upgrade packages
	 */
	public function testUpgrade(): void {
		$this->checkCommandExistence();
		$this->receiveAsyncCommand([$this, 'callback'], 'apt-get upgrade -y', true);
		Assert::noError(function (): void {
			$this->manager->upgrade([$this, 'callback']);
		});
	}

	/**
	 * Checks a package manager's command existence
	 */
	private function checkCommandExistence(): void {
		$this->receiveCommandExist('apt-get', true);
	}

	/**
	 * Just an empty callback
	 */
	public function callback(): void {
	}

}

$test = new AptGetPackageManagerTest();
$test->run();
