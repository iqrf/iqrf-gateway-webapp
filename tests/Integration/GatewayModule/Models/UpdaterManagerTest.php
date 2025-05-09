<?php

/**
 * TEST: App\GatewayModule\Models\UpdaterManager
 * @covers App\GatewayModule\Models\UpdaterManager
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

namespace Tests\Integration\GatewayModule\Models;

use App\GatewayModule\Exceptions\UnsupportedPackageManagerException;
use App\GatewayModule\Models\UpdaterManager;
use Iqrf\CommandExecutor\Tester\Traits\CommandExecutorTestCase;
use Mockery;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for tool for updating packages of IQRF Gateways
 */
final class UpdaterManagerTest extends TestCase {

	use CommandExecutorTestCase;

	/**
	 * @var UpdaterManager Tool for updating IQRF Gateway
	 */
	private UpdaterManager $manager;

	/**
	 * Tests the constructor (unsupported package manager)
	 */
	public function testConstructorFailure(): void {
		Mockery::close();
		$this->setUpCommandExecutor();
		$this->receiveCommandExist('apt-get', false);
		Assert::throws(function (): void {
			$manager = new UpdaterManager($this->commandExecutor);
			$manager->getUpgradable();
		}, UnsupportedPackageManagerException::class);
	}

	/**
	 * Tests the function to list upgradable packages
	 */
	public function testListUpgradable(): void {
		$this->receiveAsyncCommand(
			callback: [$this, 'callback'],
			command: 'apt-get -s upgrade -V',
			needSudo: true,
		);
		Assert::noError(function (): void {
			$this->manager->listUpgradable([$this, 'callback']);
		});
	}

	/**
	 * Returns list of test data for testGetUpgradable() method
	 * @return array<array<string|array<array{name: string, oldVersion: string, newVersion: string}>>> List of test data for testGetUpgradable() method
	 */
	public function getUpgradablePackages(): array {
		return array_map(
			static fn (string $fileName): array => [
				FileSystem::read(TESTER_DIR . '/data/packageManagers/apt-get/' . $fileName . '.stdout'),
				Json::decode(FileSystem::read(TESTER_DIR . '/data/packageManagers/apt-get/' . $fileName . '.json'), forceArrays: true),
			],
			['upgradablePackages', 'upgradablePackagesNone']
		);
	}

	/**
	 * Tests the function to get list of upgradable packages
	 * @dataProvider getUpgradablePackages
	 * @param string $stdout Standard output
	 * @param array<array{name: string, oldVersion: string, newVersion: string}> $packages List of upgradable packages
	 */
	public function testGetUpgradable(string $stdout, array $packages): void {
		$this->receiveCommand(
			command: 'apt-get -s upgrade -V',
			needSudo: true,
			stdout: $stdout,
		);
		Assert::same($packages, $this->manager->getUpgradable());
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
		$this->manager = new UpdaterManager($this->commandExecutor);
	}

	/**
	 * Checks a package manager's command existence
	 */
	private function checkCommandExistence(): void {
		$this->receiveCommandExist('apt-get', true);
	}

}

$test = new UpdaterManagerTest();
$test->run();
