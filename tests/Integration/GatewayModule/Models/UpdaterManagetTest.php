<?php

/**
 * TEST: App\GatewayModule\Models\UpdaterManager
 * @covers App\GatewayModule\Models\UpdaterManager
 * @phpVersion >= 7.3
 * @testCase
 */
/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

namespace Tests\Integration\GatewayModule\Models;

use App\CoreModule\Models\CommandManager;
use App\GatewayModule\Exceptions\UnsupportedPackageManagerException;
use App\GatewayModule\Models\UpdaterManager;
use Mockery;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for tool for updating packages of IQRF Gateways
 */
final class UpdaterManagetTest extends CommandTestCase {

	/**
	 * @var UpdaterManager Tool for updating IQRF Gateway
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->checkCommandExistence();
		$this->manager = new UpdaterManager($this->commandManager);
	}

	/**
	 * Tests the constructor (unsupported package manager)
	 */
	public function testConstructorFailure(): void {
		$commandManager = Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('commandExist')
			->withArgs(['apt-get'])->andReturn(false);
		Assert::throws(function () use ($commandManager): void {
			$manager = new UpdaterManager($commandManager);
			$manager->getUpgradable();
		}, UnsupportedPackageManagerException::class);
	}

	/**
	 * Tests the function to list upgradable packages
	 */
	public function testListUpgradable(): void {
		$this->receiveAsyncCommand([$this, 'callback'], 'apt-get -s upgrade -V', true);
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
		$this->receiveCommand('apt-get -s upgrade -V', true, $output);
		Assert::same($expected, $this->manager->getUpgradable());
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

$test = new UpdaterManagetTest();
$test->run();
