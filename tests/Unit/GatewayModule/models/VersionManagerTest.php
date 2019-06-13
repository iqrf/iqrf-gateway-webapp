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
	 * IQRF Gateway Daemon's version
	 */
	private const DAEMON_VERSION = 'v2.1.0';

	/**
	 * IQRF Gateway Daemon's version with build time
	 */
	private const DAEMON_VERSION_FULL = 'v2.1.0 2019-06-12T20:44:25';

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->commandManager = Mockery::mock(CommandManager::class);
		$this->manager = new VersionManager($this->commandManager, $this->request, $this->wsClient);
	}

	/**
	 * Tests the function to get IQRF Gateway Daemon's version
	 */
	public function testGetDaemonCli(): void {
		$this->commandManager->shouldReceive('commandExist')->with('iqrfgd2')->andReturn(true);
		$this->commandManager->shouldReceive('run')->with('iqrfgd2 version')->andReturn(self::DAEMON_VERSION_FULL);
		Assert::same(self::DAEMON_VERSION, $this->manager->getDaemon());
	}

	/**
	 * Tests the function to get IQRF Gateway Daemon's version
	 */
	public function testGetDaemonCliVerbose(): void {
		$this->commandManager->shouldReceive('commandExist')->with('iqrfgd2')->andReturn(true);
		$this->commandManager->shouldReceive('run')->with('iqrfgd2 version')->andReturn(self::DAEMON_VERSION_FULL);
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
		$this->commandManager->shouldReceive('commandExist')->with('iqrfgd2')->andReturn(true);
		$this->commandManager->shouldReceive('run')->with('iqrfgd2 version')->andReturn('none');
		$this->request->shouldReceive('setRequest')->with(self::DAEMON_API_REQUEST);
		$this->wsClient->shouldReceive('sendSync')->with(Mockery::type(ApiRequest::class))->andReturn($response);
		Assert::same(self::DAEMON_VERSION, $this->manager->getDaemon());
	}


	/**
	 * Tests the function to get IQRF Gateway Daemon's version (IQRF Gateway Daemon is not installed)
	 */
	public function testGetDaemonNotInstalled(): void {
		$this->commandManager->shouldReceive('commandExist')->with('iqrfgd2')->andReturn(false);
		$this->request->shouldReceive('setRequest')->with(self::DAEMON_API_REQUEST);
		$this->wsClient->shouldReceive('sendSync')->withAnyArgs()->andThrow(EmptyResponseException::class);
		Assert::same('none', $this->manager->getDaemon());
	}

	/**
	 * Tests the function to get IQRF Gateway Daemon's version (unknown version)
	 */
	public function testGetDaemonUnknown(): void {
		$this->commandManager->shouldReceive('commandExist')->with('iqrfgd2')->andReturn(true);
		$this->commandManager->shouldReceive('run')->with('iqrfgd2 version')->andReturn('');
		$this->request->shouldReceive('setRequest')->with(self::DAEMON_API_REQUEST);
		$this->wsClient->shouldReceive('sendSync')->withAnyArgs()->andThrow(EmptyResponseException::class);
		Assert::same('unknown', $this->manager->getDaemon());
	}

	/**
	 * Tests the function to get IQRF Gateway Webapp's version
	 */
	public function testGetWebapp(): void {
		Assert::same('v2.0.0-beta', $this->manager->getWebapp());
	}

	/**
	 * Tests the function to get IQRF Gateway Webapp's version (verbose)
	 */
	public function testGetWebappVerbose(): void {
		$this->commandManager->shouldReceive('run')->with('git rev-parse --is-inside-work-tree')->andReturn('true');
		$this->commandManager->shouldReceive('run')->with('git rev-parse --verify HEAD')->andReturn('commit');
		Assert::same('v2.0.0-beta (commit)', $this->manager->getWebapp(true));
	}

}

$test = new VersionManagerTest();
$test->run();
