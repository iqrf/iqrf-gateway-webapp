<?php

/**
 * TEST: App\GatewayModule\Models\VersionManager
 * @covers App\GatewayModule\Models\VersionManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
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
	 * @var VersionManager|MockInterface Version manager
	 */
	private VersionManager $manager;

	/**
	 * @var array<string, array<string, bool>|string> IQRF Gateway Daemon's API request
	 */
	private const DAEMON_API_REQUEST = [
		'mType' => 'mngDaemon_Version',
		'data' => ['returnVerbose' => true],
	];

	/**
	 * @var array<string, string> Commands for retrieving version
	 */
	private const COMMANDS = [
		'controller' => 'iqrf-gateway-controller --version',
		'daemon_new' => 'iqrfgd2 --version',
		'daemon_old' => 'iqrfgd2 version',
		'setter' => 'iqrf-gateway-setter --version',
		'uploader' => 'iqrf-gateway-uploader --version',
	];

	/**
	 * @var array<string, string> Standard outputs
	 */
	private const STDOUTS = [
		'controller' => 'iqrf-gateway-controller 0.3.4',
		'daemon_old' => 'v2.1.0 2019-06-12T20:44:25',
		'daemon_new' => 'IQRF Gateway Daemon v2.5.0-alpha',
		'setter' => 'IQRF Gateway Setter v1.0.0',
	];
	/**
	 * @var array<string, string> Expected versions
	 */
	private const VERSIONS = [
		'controller' => '0.3.4',
		'daemon_new' => 'v2.5.0-alpha',
		'daemon_old' => 'v2.1.0',
		'setter' => 'v1.0.0',
		'uploader' => 'v1.0.0',
		'webapp' => 'v2.5.7-alpha',
	];

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->commandManager = Mockery::mock(CommandManager::class);
		$this->manager = Mockery::mock(VersionManager::class, [$this->commandManager, $this->request, $this->wsClient])->makePartial();
	}

	/**
	 * Mock command existence check
	 * @param string $command Command to check
	 * @param bool $result Result of the command
	 */
	private function mockCommendExists(string $command, bool $result): void {
		$this->commandManager->shouldReceive('commandExist')
			->with($command)
			->andReturn($result);
	}

	/**
	 * Mock command execution
	 * @param string $command Command to execute
	 * @param string $stdout Standard output
	 * @param string $stderr Standard error output
	 * @param int $exitCode Exit code
	 */
	private function mockCommand(string $command, string $stdout = '', string $stderr = '', int $exitCode = 0): void {
		$this->commandManager->shouldReceive('run')
			->with($command)
			->andReturn(new Command($command, $stdout, $stderr, $exitCode));
	}

	private function mockWebappJson(string $commit = '', string $pipeline = ''): void {
		$this->manager->shouldReceive('getWebappJson')
			->andReturn([
				'version' => self::VERSIONS['webapp'],
				'commit' => $commit,
				'pipeline' => $pipeline,
			]);
	}

	/**
	 * Tests the function to get IQRF Gateway Controller's version (empty stdout)
	 */
	public function testGetControllerEmpty(): void {
		$this->mockCommendExists('iqrf-gateway-controller', true);
		$this->mockCommand(self::COMMANDS['controller']);
		Assert::null($this->manager->getController());
	}

	/**
	 * Tests the function to get IQRF Gateway Controller's version (not installed)
	 */
	public function testGetControllerNotInstalled(): void {
		$this->mockCommendExists('iqrf-gateway-controller', false);
		Assert::null($this->manager->getController());
	}

	/**
	 * Tests the function to get IQRF Gateway Controller's version
	 */
	public function testGetController(): void {
		$this->mockCommendExists('iqrf-gateway-controller', true);
		$this->mockCommand(self::COMMANDS['controller'], self::STDOUTS['controller']);
		Assert::same(self::VERSIONS['controller'], $this->manager->getController());
	}

	/**
	 * Tests the function to get IQRF Gateway Daemon's version
	 */
	public function testGetDaemonCliOld(): void {
		$this->mockCommendExists('iqrfgd2', true);
		$this->mockCommand(self::COMMANDS['daemon_old'], self::STDOUTS['daemon_old']);
		Assert::same(self::VERSIONS['daemon_old'], $this->manager->getDaemon());
	}

	/**
	 * Tests the function to get IQRF Gateway Daemon's version
	 */
	public function testGetDaemonCliNew(): void {
		$this->mockCommendExists('iqrfgd2', true);
		$this->mockCommand(self::COMMANDS['daemon_old'], '', '', 1);
		$this->mockCommand(self::COMMANDS['daemon_new'], self::STDOUTS['daemon_new']);
		Assert::same(self::VERSIONS['daemon_new'], $this->manager->getDaemon());
	}

	/**
	 * Tests the function to get IQRF Gateway Daemon's version
	 */
	public function testGetDaemonCliVerbose(): void {
		$this->mockCommendExists('iqrfgd2', true);
		$this->mockCommand(self::COMMANDS['daemon_old'], self::STDOUTS['daemon_old']);
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
		$this->mockCommendExists('iqrfgd2', true);
		$this->mockCommand(self::COMMANDS['daemon_old']);
		$this->mockCommand(self::COMMANDS['daemon_new']);
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
		$this->mockCommendExists('iqrfgd2', false);
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
		$this->mockCommendExists('iqrfgd2', true);
		$this->mockCommand(self::COMMANDS['daemon_old']);
		$this->mockCommand(self::COMMANDS['daemon_new']);
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
		$this->mockCommendExists('iqrf-gateway-setter', false);
		Assert::null($this->manager->getSetter());
	}

	/**
	 * Tests the function to get IQRF Gateway Setter's version
	 */
	public function testGetSetter(): void {
		$this->mockCommendExists('iqrf-gateway-setter', true);
		$this->mockCommand(self::COMMANDS['setter'], self::STDOUTS['setter']);
		Assert::same(self::VERSIONS['setter'], $this->manager->getSetter());
	}

	/**
	 * Tests the function to get IQRF Gateway Setter's version (empty string)
	 */
	public function testGetSetterEmptyString(): void {
		$this->mockCommendExists('iqrf-gateway-setter', true);
		$this->mockCommand(self::COMMANDS['setter'], '');
		Assert::null($this->manager->getSetter());
	}

	/**
	 * Tests the function to get IQRF Gateway Uploader's version (not installed)
	 */
	public function testGetUploaderNotInstalled(): void {
		$this->mockCommendExists('iqrf-gateway-uploader', false);
		Assert::null($this->manager->getUploader());
	}

	/**
	 * Tests the function to get IQRF Gateway Uploader's version
	 */
	public function testGetUploader(): void {
		$this->mockCommendExists('iqrf-gateway-uploader', true);
		$this->mockCommand(self::COMMANDS['uploader'], self::VERSIONS['uploader']);
		Assert::same(self::VERSIONS['uploader'], $this->manager->getUploader());
	}

	/**
	 * Tests the function to get IQRF Gateway Uploader's version (empty string)
	 */
	public function testGetUploaderEmptyString(): void {
		$this->mockCommendExists('iqrf-gateway-uploader', true);
		$this->mockCommand(self::COMMANDS['uploader'], '', '', 1);
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
		$this->mockCommand('git rev-parse --is-inside-work-tree', 'true');
		$this->mockCommand('git rev-parse --verify HEAD', 'commit');
		Assert::same(self::VERSIONS['webapp'] . ' (commit)', $this->manager->getWebapp(true));
	}

	/**
	 * Tests the function to get IQRF Gateway Webapp's version (verbose, not in git repository)
	 */
	public function testGetWebappVerboseOutsideGit(): void {
		$this->mockWebappJson();
		$this->mockCommand('git rev-parse --is-inside-work-tree', 'false');
		Assert::same(self::VERSIONS['webapp'], $this->manager->getWebapp(true));
	}

}

$test = new VersionManagerTest();
$test->run();
