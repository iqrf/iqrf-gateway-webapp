<?php

/**
 * TEST: App\ServiceModule\Models\DockerSupervisorManager
 * @covers App\ServiceModule\Models\DockerSupervisorManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\ServiceModule\Models;

use App\ServiceModule\Models\DockerSupervisorManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for supervisord service manager in a Docker container
 */
class DockerSupervisorManagerTest extends CommandTestCase {

	/**
	 * @var DockerSupervisorManager Service manager for supervisord init daemon in a Docker container
	 */
	private $manager;

	/**
	 * @var string Name of service
	 */
	private $serviceName = 'iqrf-gateway-daemon';

	/**
	 * Tests the function to start the service via supervisord in Docker container
	 */
	public function testStartDockerSupervisorD(): void {
		$command = 'supervisorctl start ' . $this->serviceName;
		$this->receiveCommand($command, true);
		Assert::noError([$this->manager, 'start']);
	}

	/**
	 * Tests the function to stop the service via supervisord in Docker container
	 */
	public function testStop(): void {
		$command = 'supervisorctl stop ' . $this->serviceName;
		$this->receiveCommand($command, true);
		Assert::noError([$this->manager, 'stop']);
	}

	/**
	 * Tests the function to restart the service via supervisord in Docker container
	 */
	public function testRestart(): void {
		$command = 'supervisorctl restart ' . $this->serviceName;
		$this->receiveCommand($command, true);
		Assert::noError([$this->manager, 'restart']);
	}

	/**
	 * Tests the function to get status of the service via supervisord in Docker container
	 */
	public function testGetStatus(): void {
		$expected = 'status';
		$command = 'supervisorctl status ' . $this->serviceName;
		$this->receiveCommand($command, true, $expected);
		Assert::same($expected, $this->manager->getStatus());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new DockerSupervisorManager($this->commandManager, $this->serviceName);
	}

}

$test = new DockerSupervisorManagerTest();
$test->run();
