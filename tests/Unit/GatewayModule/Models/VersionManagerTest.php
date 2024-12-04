<?php

/**
 * TEST: App\GatewayModule\Models\VersionManager
 * @covers App\GatewayModule\Models\VersionManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

use App\GatewayModule\Models\VersionManager;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Requests\ApiRequest;
use Iqrf\CommandExecutor\Tester\Traits\CommandExecutorTestCase;
use Mockery;
use Mockery\MockInterface;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use Tester\Assert;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for version manager
 */
final class VersionManagerTest extends WebSocketTestCase {

	use CommandExecutorTestCase;

	/**
	 * IQRF Gateway Daemon's API request
	 */
	private const DAEMON_API_REQUEST = [
		'mType' => 'mngDaemon_Version',
		'data' => ['returnVerbose' => true],
	];

	/**
	 * Commands for retrieving version
	 */
	private const COMMANDS = [
		'controller' => 'iqrf-gateway-controller --version',
		'daemon_new' => 'iqrfgd2 --version',
		'daemon_old' => 'iqrfgd2 version',
		'influxdb-bridge' => 'iqrf-gateway-influxdb-bridge --version',
		'setter' => 'iqrf-gateway-setter --version',
		'uploader' => 'iqrf-gateway-uploader --version',
	];

	/**
	 * Standard outputs
	 */
	private const STDOUTS = [
		'controller' => 'iqrf-gateway-controller 0.3.4',
		'daemon_old' => 'v2.1.0 2019-06-12T20:44:25',
		'daemon_new' => 'IQRF Gateway Daemon v2.5.0-alpha',
		'influxdb-bridge' => 'IQRF Gateway InfluxDB Bridge v1.2.0-alpha',
		'setter' => 'IQRF Gateway Setter v1.0.0',
	];

	/**
	 * Expected versions
	 */
	private const VERSIONS = [
		'controller' => '0.3.4',
		'daemon_new' => 'v2.5.0-alpha',
		'daemon_old' => 'v2.1.0',
		'influxdb-bridge' => 'v1.2.0-alpha',
		'setter' => 'v1.0.0',
		'uploader' => 'v1.0.0',
		'webapp' => 'v3.0.0-alpha',
	];

	/**
	 * @var VersionManager|MockInterface Version manager
	 */
	private MockInterface|VersionManager $manager;

	/**
	 * Tests the function to get IQRF Gateway Controller's version (empty stdout)
	 */
	public function testGetControllerEmpty(): void {
		$this->receiveCommandExist('iqrf-gateway-controller', true);
		$this->receiveCommand(command: self::COMMANDS['controller']);
		Assert::null($this->manager->getController());
	}

	/**
	 * Tests the function to get IQRF Gateway Controller's version (not installed)
	 */
	public function testGetControllerNotInstalled(): void {
		$this->receiveCommandExist('iqrf-gateway-controller', false);
		Assert::null($this->manager->getController());
	}

	/**
	 * Tests the function to get IQRF Gateway Controller's version
	 */
	public function testGetController(): void {
		$this->receiveCommandExist('iqrf-gateway-controller', true);
		$this->receiveCommand(
			command: self::COMMANDS['controller'],
			stdout: self::STDOUTS['controller'],
		);
		Assert::same(self::VERSIONS['controller'], $this->manager->getController());
	}

	/**
	 * Tests the function to get IQRF Gateway Daemon's version
	 */
	public function testGetDaemonCliOld(): void {
		$this->receiveCommandExist('iqrfgd2', true);
		$this->receiveCommand(
			command: self::COMMANDS['daemon_old'],
			stdout: self::STDOUTS['daemon_old'],
		);
		Assert::same(self::VERSIONS['daemon_old'], $this->manager->getDaemon());
	}

	/**
	 * Tests the function to get IQRF Gateway Daemon's version
	 */
	public function testGetDaemonCliNew(): void {
		$this->receiveCommandExist('iqrfgd2', true);
		$this->receiveCommand(
			command: self::COMMANDS['daemon_old'],
			exitCode: 1,
		);
		$this->receiveCommand(
			command: self::COMMANDS['daemon_new'],
			stdout: self::STDOUTS['daemon_new'],
		);
		Assert::same(self::VERSIONS['daemon_new'], $this->manager->getDaemon());
	}

	/**
	 * Tests the function to get IQRF Gateway Daemon's version
	 */
	public function testGetDaemonCliVerbose(): void {
		$this->receiveCommandExist('iqrfgd2', true);
		$this->receiveCommand(
			command: self::COMMANDS['daemon_old'],
			stdout: self::STDOUTS['daemon_old'],
		);
		Assert::same(self::STDOUTS['daemon_old'], $this->manager->getDaemon(true));
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
						'version' => self::STDOUTS['daemon_old'],
					],
					'insId' => 'iqrfgd2-default',
					'statusStr' => 'ok',
					'status' => 0,
				],
			],
		];
		$this->receiveCommandExist('iqrfgd2', true);
		$this->receiveCommand(command: self::COMMANDS['daemon_old']);
		$this->receiveCommand(command: self::COMMANDS['daemon_new']);
		$this->request->shouldReceive('set')
			->with(self::DAEMON_API_REQUEST);
		$this->wsClient->shouldReceive('sendSync')
			->with(Mockery::type(ApiRequest::class))
			->andReturn($response);
		Assert::same(self::VERSIONS['daemon_old'], $this->manager->getDaemon());
	}

	/**
	 * Tests the function to get IQRF Gateway Daemon's version (IQRF Gateway Daemon is not installed)
	 */
	public function testGetDaemonNotInstalled(): void {
		$this->receiveCommandExist('iqrfgd2', false);
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
		$this->receiveCommandExist('iqrfgd2', true);
		$this->receiveCommand(command: self::COMMANDS['daemon_old']);
		$this->receiveCommand(command: self::COMMANDS['daemon_new']);
		$this->request->shouldReceive('set')
			->with(self::DAEMON_API_REQUEST);
		$this->wsClient->shouldReceive('sendSync')
			->withAnyArgs()
			->andThrow(EmptyResponseException::class);
		Assert::same('unknown', $this->manager->getDaemon());
	}

	/**
	 * Tests the function to get IQRF Gateway InfluxDB Bridge's version (not installed)
	 */
	public function testGetInfluxdbBridgeNotInstalled(): void {
		$this->receiveCommandExist('iqrf-gateway-influxdb-bridge', false);
		Assert::null($this->manager->getInfluxdbBridge());
	}

	/**
	 * Tests the function to get IQRF Gateway InfluxDB Bridge's version
	 */
	public function testGetInfluxdbBridge(): void {
		$this->receiveCommandExist('iqrf-gateway-influxdb-bridge', true);
		$this->receiveCommand(
			command: self::COMMANDS['influxdb-bridge'],
			stdout: self::STDOUTS['influxdb-bridge'],
		);
		Assert::same(self::VERSIONS['influxdb-bridge'], $this->manager->getInfluxdbBridge());
	}

	/**
	 * Tests the function to get IQRF Gateway Setter's version (not installed)
	 */
	public function testGetSetterNotInstalled(): void {
		$this->receiveCommandExist('iqrf-gateway-setter', false);
		Assert::null($this->manager->getSetter());
	}

	/**
	 * Tests the function to get IQRF Gateway Setter's version
	 */
	public function testGetSetter(): void {
		$this->receiveCommandExist('iqrf-gateway-setter', true);
		$this->receiveCommand(
			command: self::COMMANDS['setter'],
			stdout: self::STDOUTS['setter'],
		);
		Assert::same(self::VERSIONS['setter'], $this->manager->getSetter());
	}

	/**
	 * Tests the function to get IQRF Gateway Setter's version (empty string)
	 */
	public function testGetSetterEmptyString(): void {
		$this->receiveCommandExist('iqrf-gateway-setter', true);
		$this->receiveCommand(command: self::COMMANDS['setter']);
		Assert::null($this->manager->getSetter());
	}

	/**
	 * Tests the function to get IQRF Gateway Uploader's version (not installed)
	 */
	public function testGetUploaderNotInstalled(): void {
		$this->receiveCommandExist('iqrf-gateway-uploader', false);
		Assert::null($this->manager->getUploader());
	}

	/**
	 * Tests the function to get IQRF Gateway Uploader's version
	 */
	public function testGetUploader(): void {
		$this->receiveCommandExist('iqrf-gateway-uploader', true);
		$this->receiveCommand(
			command: self::COMMANDS['uploader'],
			stdout: self::VERSIONS['uploader'],
		);
		Assert::same(self::VERSIONS['uploader'], $this->manager->getUploader());
	}

	/**
	 * Tests the function to get IQRF Gateway Uploader's version (empty string)
	 */
	public function testGetUploaderEmptyString(): void {
		$this->receiveCommandExist('iqrf-gateway-uploader', true);
		$this->receiveCommand(
			command: self::COMMANDS['uploader'],
			exitCode: 1,
		);
		Assert::null($this->manager->getUploader());
	}

	/**
	 * Tests the function to get IQRF Gateway Webapp's version
	 */
	public function testGetWebapp(): void {
		$this->mockWebappJson();
		Assert::same(self::VERSIONS['webapp'], $this->manager->getWebapp());
	}

	/**
	 * Tests the function to get IQRF Gateway Webapp's version (invalid JSON)
	 */
	public function testGetWebappFailure(): void {
		$this->manager->shouldReceive('getWebappJson')
			->andThrow(JsonException::class);
		Assert::same('unknown', $this->manager->getWebapp());
	}

	/**
	 * Tests the function to get IQRF Gateway Webapp's version (with GitLab CI pipeline ID)
	 */
	public function testGetWebappPipeline(): void {
		$version = self::VERSIONS['webapp'];
		$pipelineId = '42';
		if (Strings::match($version, '#^[A-Za-z0-9.]*\-(alpha|beta|dev|rc)[A-Za-z0-9]*$#i') !== null) {
			$version .= '~' . $pipelineId;
		}
		$this->mockWebappJson('', $pipelineId);
		Assert::same($version, $this->manager->getWebapp());
	}

	/**
	 * Tests the function to get IQRF Gateway Webapp's version (verbose)
	 */
	public function testGetWebappVerboseFile(): void {
		$this->mockWebappJson('commit');
		Assert::same(self::VERSIONS['webapp'] . ' (commit)', $this->manager->getWebapp(true));
	}

	/**
	 * Tests the function to get IQRF Gateway Webapp's version (verbose)
	 */
	public function testGetWebappVerboseGit(): void {
		$this->mockWebappJson();
		$this->receiveCommand(
			command: 'git rev-parse --is-inside-work-tree',
			stdout: 'true',
		);
		$this->receiveCommand(
			command: 'git rev-parse --verify HEAD',
			stdout: 'commit',
		);
		Assert::same(self::VERSIONS['webapp'] . ' (commit)', $this->manager->getWebapp(true));
	}

	/**
	 * Tests the function to get IQRF Gateway Webapp's version (verbose, not in git repository)
	 */
	public function testGetWebappVerboseOutsideGit(): void {
		$this->mockWebappJson();
		$this->receiveCommand(
			command: 'git rev-parse --is-inside-work-tree',
			stdout: 'false',
		);
		Assert::same(self::VERSIONS['webapp'], $this->manager->getWebapp(true));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->setUpCommandExecutor();
		$this->manager = Mockery::mock(VersionManager::class, [$this->commandExecutor, $this->request, $this->wsClient])->makePartial();
	}

	/**
	 * Mock IQRF Gateway Webapps version JSON file
	 * @param string $commit Git commit hash
	 * @param string $pipeline GitLab CI pipeline ID
	 */
	private function mockWebappJson(string $commit = '', string $pipeline = ''): void {
		$this->manager->shouldReceive('getWebappJson')
			->andReturn([
				'version' => self::VERSIONS['webapp'],
				'commit' => $commit,
				'pipeline' => $pipeline,
			]);
	}

}

$test = new VersionManagerTest();
$test->run();
