<?php

/**
 * TEST: App\ServiceModule\Model\ServiceManager
 * @covers App\ServiceModule\Model\ServiceManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ServiceModule\Model;

use App\CoreModule\Model\CommandManager;
use App\ServiceModule\Exception\NotSupportedInitSystemException;
use App\ServiceModule\Model\ServiceManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for service manager
 */
class ServiceManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var \Mockery\MockInterface Mocked command manager
	 */
	private $commandManager;

	/**
	 * @var ServiceManager Service manager for supervisord init daemon in a Docker container
	 */
	private $managerDocker;

	/**
	 * @var ServiceManager Service manager for systemD init daemon
	 */
	private $managerSystemD;

	/**
	 * @var ServiceManager Service manager for unknown init daemon
	 */
	private $managerUnknown;

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp() {
		$this->commandManager = \Mockery::mock(CommandManager::class);
		$this->managerDocker = new ServiceManager('docker-supervisor', $this->commandManager);
		$this->managerSystemD = new ServiceManager('systemd', $this->commandManager);
		$this->managerUnknown = new ServiceManager('unknown', $this->commandManager);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown() {
		\Mockery::close();
	}

	/**
	 * Test function to start IQRF Gateway Daemon's service via systemD
	 */
	public function testStartSystemD() {
		$this->commandManager->shouldReceive('send')->with('systemctl start iqrfgd2.service', true)->andReturn(true);
		Assert::true($this->managerSystemD->start());
	}

	/**
	 * Test function to start IQRF Gateway Daemon's service via supervisord in Docker container
	 */
	public function testStartDockerSupervisorD() {
		$this->commandManager->shouldReceive('send')->with('supervisorctl start iqrfgd2', true)->andReturn(true);
		Assert::true($this->managerDocker->start());
	}

	/**
	 * Test function to start IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testStartUnknown() {
		Assert::exception([$this->managerUnknown, 'start'], NotSupportedInitSystemException::class);
	}

	/**
	 * Test function to stop IQRF Gateway Daemon's service via systemD
	 */
	public function testStopSystemD() {
		$this->commandManager->shouldReceive('send')->with('systemctl stop iqrfgd2.service', true)->andReturn(true);
		Assert::true($this->managerSystemD->stop());
	}

	/**
	 * Test function to stop IQRF Gateway Daemon's service via supervisord in Docker container
	 */
	public function testStopDockerSupervisorD() {
		$this->commandManager->shouldReceive('send')->with('supervisorctl stop iqrfgd2', true)->andReturn(true);
		Assert::true($this->managerDocker->stop());
	}

	/**
	 * Test function to stop IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testStopUnknown() {
		Assert::exception([$this->managerUnknown, 'stop'], NotSupportedInitSystemException::class);
	}

	/**
	 * Test function to restart IQRF Gateway Daemon's service via systemD
	 */
	public function testRestartSystemD() {
		$this->commandManager->shouldReceive('send')->with('systemctl restart iqrfgd2.service', true)->andReturn(true);
		Assert::true($this->managerSystemD->restart());
	}

	/**
	 * Test function to restart IQRF Gateway Daemon's service via supervisord in Docker container
	 */
	public function testRestartDockerSupervisorD() {
		$this->commandManager->shouldReceive('send')->with('supervisorctl restart iqrfgd2', true)->andReturn(true);
		Assert::true($this->managerDocker->restart());
	}

	/**
	 * Test function to restart IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testRestartUnknown() {
		Assert::exception([$this->managerUnknown, 'restart'], NotSupportedInitSystemException::class);
	}

	/**
	 * Test function to get status of IQRF Gateway Daemon's service via systemD
	 */
	public function testGetStatusSystemD() {
		$this->commandManager->shouldReceive('send')->with('systemctl status iqrfgd2.service', true)->andReturn(true);
		Assert::true($this->managerSystemD->getStatus());
	}

	/**
	 * Test function to get status of IQRF Gateway Daemon's service via supervisord in Docker container
	 */
	public function testGetStatusDockerSupervisorD() {
		$this->commandManager->shouldReceive('send')->with('supervisorctl status iqrfgd2', true)->andReturn(true);
		Assert::true($this->managerDocker->getStatus());
	}

	/**
	 * Test function to get status of IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testGetStatusUnknown() {
		Assert::exception([$this->managerUnknown, 'getStatus'], NotSupportedInitSystemException::class);
	}

}

$test = new ServiceManagerTest($container);
$test->run();
