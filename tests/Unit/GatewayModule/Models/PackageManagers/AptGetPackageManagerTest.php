<?php

/**
 * TEST: App\GatewayModule\Models\PackageManagers\AptGetPackageManager
 * @covers App\GatewayModule\Models\PackageManagers\AptGetPackageManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types = 1);

namespace Tests\Unit\GatewayModule\Models\PackageManagers;

use App\GatewayModule\Exceptions\UnsupportedPackageManagerException;
use App\GatewayModule\Models\PackageManagers\AptGetPackageManager;
use Iqrf\CommandExecutor\Tester\Traits\CommandExecutorTestCase;
use Mockery;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for tool for apt-get package manager
 */
final class AptGetPackageManagerTest extends TestCase {

	use CommandExecutorTestCase;

	/**
	 * Packages
	 */
	private const PACKAGES = ['iqrf-gateway-daemon', 'iqrf-gateway-webapp'];

	/**
	 * @var AptGetPackageManager Tool for updating IQRF Gateway
	 */
	private AptGetPackageManager $manager;

	/**
	 * Tests the constructor (failure)
	 */
	public function testConstructorFailure(): void {
		Mockery::close();
		$this->setUpCommandExecutor();
		$this->receiveCommandExist('apt-get', false);
		Assert::throws(function (): void {
			new AptGetPackageManager($this->commandExecutor);
		}, UnsupportedPackageManagerException::class);
	}

	/**
	 * Tests the function to install packages
	 */
	public function testInstall(): void {
		$command = 'apt-get install -y \'iqrf-gateway-daemon\' \'iqrf-gateway-webapp\'';
		$this->receiveAsyncCommand(
			callback: [$this, 'callback'],
			command: $command,
			needSudo: true,
		);
		Assert::noError(function (): void {
			$this->manager->install([$this, 'callback'], self::PACKAGES);
		});
	}

	/**
	 * Tests the function to list upgradable packages
	 */
	public function testListUpgradable(): void {
		$command = 'apt-get -s upgrade -V';
		$this->receiveAsyncCommand(
			callback: [$this, 'callback'],
			command: $command,
			needSudo: true,
		);
		Assert::noError(function (): void {
			$this->manager->listUpgradable([$this, 'callback']);
		});
	}

	/**
	 * Tests the function to get list of upgradable packages
	 */
	public function testGetUpgradable(): void {
		$path = TESTER_DIR . '/data/packageManagers/apt-get/';
		$expected = Json::decode(FileSystem::read($path . 'upgradablePackages.json'), forceArrays: true);
		$this->receiveCommand(
			command: 'apt-get -s upgrade -V',
			needSudo: true,
			stdout: FileSystem::read($path . 'upgradablePackages.stdout'),
		);
		Assert::same($expected, $this->manager->getUpgradable());
	}

	/**
	 * Tests the function to remove packages
	 */
	public function testRemove(): void {
		$command = 'apt-get remove -y \'iqrf-gateway-daemon\' \'iqrf-gateway-webapp\'';
		$this->receiveAsyncCommand(
			callback: [$this, 'callback'],
			command: $command,
			needSudo: true,
		);
		Assert::noError(function (): void {
			$this->manager->remove([$this, 'callback'], self::PACKAGES);
		});
	}

	/**
	 * Tests the function to purge packages
	 */
	public function testPurge(): void {
		$command = 'apt-get purge -y \'iqrf-gateway-daemon\' \'iqrf-gateway-webapp\'';
		$this->receiveAsyncCommand(
			callback: [$this, 'callback'],
			command: $command,
			needSudo: true,
		);
		Assert::noError(function (): void {
			$this->manager->purge([$this, 'callback'], self::PACKAGES);
		});
	}

	/**
	 * Tests the function to update list of packages
	 */
	public function testUpdate(): void {
		$this->receiveAsyncCommand(
			callback: [$this, 'callback'],
			command: 'apt-get update',
			needSudo: true,
		);
		Assert::noError(function (): void {
			$this->manager->update([$this, 'callback']);
		});
	}

	/**
	 * Tests the function to upgrade packages
	 */
	public function testUpgrade(): void {
		$this->receiveAsyncCommand(
			callback: [$this, 'callback'],
			command: 'apt-get upgrade -y',
			needSudo: true,
		);
		Assert::noError(function (): void {
			$this->manager->upgrade([$this, 'callback']);
		});
	}

	/**
	 * Just an empty callback
	 */
	public function callback(): void {
		// Empty callback
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->setUpCommandExecutor();
		$this->checkCommandExistence();
		$this->manager = new AptGetPackageManager($this->commandExecutor);
	}

	/**
	 * Checks a package manager's command existence
	 */
	private function checkCommandExistence(): void {
		$this->receiveCommandExist('apt-get', true);
	}

}

$test = new AptGetPackageManagerTest();
$test->run();
