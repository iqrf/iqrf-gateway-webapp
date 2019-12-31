<?php
/**
 * TEST: App\GatewayModule\Models\VersionManager
 * @covers App\GatewayModule\Models\VersionManager
 * @phpVersion >= 7.1
 * @testCase
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
class VersionManagerTest extends WebSocketTestCase {

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
	private const DAEMON_VERSION = 'v2.1.0';

	/**
	 * IQRF Gateway Daemon's version command
	 */
	private const DAEMON_VERSION_CMD = 'iqrfgd2 version';

	/**
	 * IQRF Gateway Daemon's version with build time
	 */
	private const DAEMON_VERSION_FULL = 'v2.1.0 2019-06-12T20:44:25';

	/**
	 * IQRF Gateway Webapp's version
	 */
	private const WEBAPP_VERSION = 'v2.0.0-rc1';

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->commandManager = Mockery::mock(CommandManager::class);
		$this->manager = new VersionManager($this->commandManager, $this->request, $this->wsClient);
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
	public function testGetDaemonCli(): void {
		$this->commandManager->shouldReceive('commandExist')
			->with('iqrfgd2')
			->andReturn(true);
		$this->commandManager->shouldReceive('run')
			->with(self::DAEMON_VERSION_CMD)
			->andReturn(new Command(self::DAEMON_VERSION_CMD, self::DAEMON_VERSION_FULL, '', 0));
		Assert::same(self::DAEMON_VERSION, $this->manager->getDaemon());
	}

	/**
	 * Tests the function to get IQRF Gateway Daemon's version
	 */
	public function testGetDaemonCliVerbose(): void {
		$this->commandManager->shouldReceive('commandExist')
			->with('iqrfgd2')
			->andReturn(true);
		$this->commandManager->shouldReceive('run')
			->with(self::DAEMON_VERSION_CMD)
			->andReturn(new Command(self::DAEMON_VERSION_CMD, self::DAEMON_VERSION_FULL, '', 0));
		Assert::same(self::DAEMON_VERSION_FULL, $this->manager->getDaemon(true));
	}

	/**
	 * Tests the function to get IQRF Gateway Daemon's version
	 */
	public function testGetDaemonWs(): void {
		$response = [
			'response' => [
				'mType' => 'mngDaemon_Version',
				'data' => [
					'rsp' => [
						'version' => self::DAEMON_VERSION_FULL,
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
			->with(self::DAEMON_VERSION_CMD)
			->andReturn(new Command(self::DAEMON_VERSION_CMD, 'none', '', 0));
		$this->request->shouldReceive('setRequest')
			->with(self::DAEMON_API_REQUEST);
		$this->wsClient->shouldReceive('sendSync')
			->with(Mockery::type(ApiRequest::class))
			->andReturn($response);
		Assert::same(self::DAEMON_VERSION, $this->manager->getDaemon());
	}


	/**
	 * Tests the function to get IQRF Gateway Daemon's version (IQRF Gateway Daemon is not installed)
	 */
	public function testGetDaemonNotInstalled(): void {
		$this->commandManager->shouldReceive('commandExist')
			->with('iqrfgd2')
			->andReturn(false);
		$this->request->shouldReceive('setRequest')
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
			->with(self::DAEMON_VERSION_CMD)
			->andReturn(new Command(self::DAEMON_VERSION_CMD, '', '', 1));
		$this->request->shouldReceive('setRequest')
			->with(self::DAEMON_API_REQUEST);
		$this->wsClient->shouldReceive('sendSync')
			->withAnyArgs()
			->andThrow(EmptyResponseException::class);
		Assert::same('unknown', $this->manager->getDaemon());
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
