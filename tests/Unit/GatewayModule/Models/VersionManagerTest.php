<?php

/**
 * TEST: App\GatewayModule\Models\VersionManager
 * @covers App\GatewayModule\Models\VersionManager
 * @phpVersion >= 7.3
 * @testCase
 */
/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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

namespace Tests\Unit\GatewayModule\Models;

use App\CoreModule\Models\CommandManager;
use App\GatewayModule\Models\VersionManager;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Requests\ApiRequest;
use Mockery;
use Mockery\MockInterface;
use Tester\Assert;
use Tests\Stubs\CoreModule\Models\Command;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for version manager
 */
final class VersionManagerTest extends WebSocketTestCase {

	/**
	 * @var CommandManager|MockInterface Mocked command manager
	 */
	private $commandManager;

	/**
	 * @var VersionManager Version manager
	 */
	private $manager;

	/**
	 * IQRF Gateway Daemon's API request
	 */
	private const DAEMON_API_REQUEST = [
		'mType' => 'mngDaemon_Version',
		'data' => ['returnVerbose' => true],
	];

	/**
	 * IQRF Gateway Controller's version
	 */
	private const CONTROLLER_VERSION = '0.3.4';

	/**
	 * IQRF Gateway Controller's version command
	 */
	private const CONTROLLER_VERSION_CMD = 'iqrf-gateway-controller --version';

	/**
	 * IQRF Gateway Daemon's version
	 */
	private const OLD_DAEMON_VERSION = 'v2.1.0';

	/**
	 * IQRF Gateway Daemon's version command
	 */
	private const OLD_DAEMON_VERSION_CMD = 'iqrfgd2 version';

	/**
	 * IQRF Gateway Daemon's version with build time
	 */
	private const OLD_DAEMON_VERSION_FULL = 'v2.1.0 2019-06-12T20:44:25';

	/**
	 * IQRF Gateway Daemon's version
	 */
	private const NEW_DAEMON_VERSION = 'v2.4.1-alpha';

	/**
	 * IQRF Gateway Daemon's version command
	 */
	private const NEW_DAEMON_VERSION_CMD = 'iqrfgd2 --version';

	/**
	 * IQRF Gateway Daemon's version string
	 */
	private const NEW_DAEMON_VERSION_STR = 'IQRF Gateway Daemon v2.4.1-alpha';

	/**
	 * IQRF Gateway Setter's version
	 */
	private const SETTER_VERSION = 'v1.0.0';

	/**
	 * IQRF Gateway Setter's version command
	 */
	private const SETTER_VERSION_CMD = 'iqrf-gateway-setter --version';

	/**
	 * IQRF Gateway Setter's version string
	 */
	private const SETTER_VERSION_STR = 'IQRF Gateway Setter v1.0.0';

	/**
	 * IQRF Gateway Uploader's version
	 */
	private const UPLOADER_VERSION = 'v1.0.0';

	/**
	 * IQRF Gateway Uploader's version command
	 */
	private const UPLOADER_VERSION_CMD = 'iqrf-gateway-uploader --version';

	/**
	 * IQRF Gateway Webapp's version
	 */
	private const WEBAPP_VERSION = 'v2.4.19-alpha';

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->commandManager = Mockery::mock(CommandManager::class);
		$this->manager = new VersionManager($this->commandManager, $this->request, $this->wsClient);
	}

	/**
	 * Tests the function to get IQRF Gateway Controller's version (empty stdout)
	 */
	public function testGetControllerEmpty(): void {
		$this->commandManager->shouldReceive('commandExist')
			->with('iqrf-gateway-controller')
			->andReturn(true);
		$this->commandManager->shouldReceive('run')
			->with(self::CONTROLLER_VERSION_CMD)
			->andReturn(new Command(self::CONTROLLER_VERSION_CMD, '', '', 0));
		Assert::null($this->manager->getController());
	}

	/**
	 * Tests the function to get IQRF Gateway Controller's version (not installed)
	 */
	public function testGetControllerNotInstalled(): void {
		$this->commandManager->shouldReceive('commandExist')
			->with('iqrf-gateway-controller')
			->andReturnFalse();
		Assert::null($this->manager->getController());
	}

	/**
	 * Tests the function to get IQRF Gateway Controller's version
	 */
	public function testGetController(): void {
		$this->commandManager->shouldReceive('commandExist')
			->with('iqrf-gateway-controller')
			->andReturn(true);
		$this->commandManager->shouldReceive('run')
			->with(self::CONTROLLER_VERSION_CMD)
			->andReturn(new Command(self::CONTROLLER_VERSION_CMD, 'iqrf-gateway-controller 0.3.4', '', 0));
		Assert::same(self::CONTROLLER_VERSION, $this->manager->getController());
	}

	/**
	 * Tests the function to get IQRF Gateway Daemon's version
	 */
	public function testGetDaemonCliOld(): void {
		$this->commandManager->shouldReceive('commandExist')
			->with('iqrfgd2')
			->andReturn(true);
		$this->commandManager->shouldReceive('run')
			->with(self::OLD_DAEMON_VERSION_CMD)
			->andReturn(new Command(self::OLD_DAEMON_VERSION_CMD, self::OLD_DAEMON_VERSION_FULL, '', 0));
		Assert::same(self::OLD_DAEMON_VERSION, $this->manager->getDaemon());
	}

	/**
	 * Tests the function to get IQRF Gateway Daemon's version
	 */
	public function testGetDaemonCliNew(): void {
		$this->commandManager->shouldReceive('commandExist')
			->with('iqrfgd2')
			->andReturn(true);
		$this->commandManager->shouldReceive('run')
			->with(self::OLD_DAEMON_VERSION_CMD)
			->andReturn(new Command(self::OLD_DAEMON_VERSION_CMD, '', '', 1));
		$this->commandManager->shouldReceive('run')
			->with(self::NEW_DAEMON_VERSION_CMD)
			->andReturn(new Command(self::NEW_DAEMON_VERSION_CMD, self::NEW_DAEMON_VERSION_STR, '', 0));
		Assert::same(self::NEW_DAEMON_VERSION, $this->manager->getDaemon());
	}

	/**
	 * Tests the function to get IQRF Gateway Daemon's version
	 */
	public function testGetDaemonCliVerbose(): void {
		$this->commandManager->shouldReceive('commandExist')
			->with('iqrfgd2')
			->andReturn(true);
		$this->commandManager->shouldReceive('run')
			->with(self::OLD_DAEMON_VERSION_CMD)
			->andReturn(new Command(self::OLD_DAEMON_VERSION_CMD, self::OLD_DAEMON_VERSION_FULL, '', 0));
		Assert::same(self::OLD_DAEMON_VERSION_FULL, $this->manager->getDaemon(true));
	}

	/**
	 * Tests the function to get IQRF Gateway Daemon's version
	 */
	public function testGetDaemonWs(): void {
		$response = [
			'response' => (object) [
				'mType' => 'mngDaemon_Version',
				'data' => (object) [
					'rsp' => (object) [
						'version' => self::OLD_DAEMON_VERSION_FULL,
					],
					'insId' => 'iqrfgd2-default',
					'statusStr' => 'ok',
					'status' => 0,
				],
			],
		];
		$this->commandManager->shouldReceive('commandExist')
			->with('iqrfgd2')
			->andReturn(true);
		$this->commandManager->shouldReceive('run')
			->with(self::OLD_DAEMON_VERSION_CMD)
			->andReturn(new Command(self::OLD_DAEMON_VERSION_CMD, 'none', '', 0));
		$this->commandManager->shouldReceive('run')
			->with(self::NEW_DAEMON_VERSION_CMD)
			->andReturn(new Command(self::NEW_DAEMON_VERSION_CMD, 'none', '', 0));
		$this->request->shouldReceive('set')
			->with(self::DAEMON_API_REQUEST);
		$this->wsClient->shouldReceive('sendSync')
			->with(Mockery::type(ApiRequest::class))
			->andReturn($response);
		Assert::same(self::OLD_DAEMON_VERSION, $this->manager->getDaemon());
	}


	/**
	 * Tests the function to get IQRF Gateway Daemon's version (IQRF Gateway Daemon is not installed)
	 */
	public function testGetDaemonNotInstalled(): void {
		$this->commandManager->shouldReceive('commandExist')
			->with('iqrfgd2')
			->andReturn(false);
		$this->request->shouldReceive('set')
			->with(self::DAEMON_API_REQUEST);
		$this->wsClient->shouldReceive('sendSync')
			->withAnyArgs()
			->andThrow(EmptyResponseException::class);
		Assert::same('none', $this->manager->getDaemon());
	}

	/**
	 * Tests the function to get IQRF Gateway Daemon's version (unknown version)
	 */
	public function testGetDaemonUnknown(): void {
		$this->commandManager->shouldReceive('commandExist')
			->with('iqrfgd2')
			->andReturn(true);
		$this->commandManager->shouldReceive('run')
			->with(self::OLD_DAEMON_VERSION_CMD)
			->andReturn(new Command(self::OLD_DAEMON_VERSION_CMD, '', '', 0));
		$this->commandManager->shouldReceive('run')
			->with(self::NEW_DAEMON_VERSION_CMD)
			->andReturn(new Command(self::NEW_DAEMON_VERSION_CMD, '', '', 0));
		$this->request->shouldReceive('set')
			->with(self::DAEMON_API_REQUEST);
		$this->wsClient->shouldReceive('sendSync')
			->withAnyArgs()
			->andThrow(EmptyResponseException::class);
		Assert::same('unknown', $this->manager->getDaemon());
	}

	/**
	 * Tests the function to get IQRF Gateway Setter's version (not installed)
	 */
	public function testGetSetterNotInstalled(): void {
		$this->commandManager->shouldReceive('commandExist')
			->with('iqrf-gateway-setter')
			->andReturnFalse();
		Assert::null($this->manager->getSetter());
	}

	/**
	 * Tests the function to get IQRF Gateway Setter's version
	 */
	public function testGetSetter(): void {
		$this->commandManager->shouldReceive('commandExist')
			->with('iqrf-gateway-setter')
			->andReturnTrue();
		$this->commandManager->shouldReceive('run')
			->with(self::SETTER_VERSION_CMD)
			->andReturn(new Command(self::SETTER_VERSION_CMD, self::SETTER_VERSION_STR, '', 0));
		Assert::same(self::SETTER_VERSION, $this->manager->getSetter());
	}

	/**
	 * Tests the function to get IQRF Gateway Uploader's version (not installed)
	 */
	public function testGetUploaderNotInstalled(): void {
		$this->commandManager->shouldReceive('commandExist')
			->with('iqrf-gateway-uploader')
			->andReturnFalse();
		Assert::null($this->manager->getUploader());
	}

	/**
	 * Tests the function to get IQRF Gateway Webapp's version
	 */
	public function testGetUploader(): void {
		$this->commandManager->shouldReceive('commandExist')
			->with('iqrf-gateway-uploader')
			->andReturnTrue();
		$this->commandManager->shouldReceive('run')
			->with(self::UPLOADER_VERSION_CMD)
			->andReturn(new Command(self::UPLOADER_VERSION_CMD, 'v1.0.0', '', 0));
		Assert::same(self::UPLOADER_VERSION, $this->manager->getUploader());
	}

	/**
	 * Tests the function to get IQRF Gateway Webapp's version
	 */
	public function testGetWebapp(): void {
		Assert::same(self::WEBAPP_VERSION, $this->manager->getWebapp());
	}

	/**
	 * Tests the function to get IQRF Gateway Webapp's version (verbose)
	 */
	public function testGetWebappVerbose(): void {
		$command = 'git rev-parse --is-inside-work-tree';
		$this->commandManager->shouldReceive('run')
			->with($command)
			->andReturn(new Command($command, 'true', '', 0));
		$command = 'git rev-parse --verify HEAD';
		$this->commandManager->shouldReceive('run')
			->with($command)
			->andReturn(new Command($command, 'commit', '', 0));
		Assert::same(self::WEBAPP_VERSION . ' (commit)', $this->manager->getWebapp(true));
	}

}

$test = new VersionManagerTest();
$test->run();
