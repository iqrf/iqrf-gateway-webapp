<?php

/**
 * TEST: App\GatewayModule\Models\RootManager
 * @covers App\GatewayModule\Models\RootManager
 * @phpVersion >= 7.3
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\GatewayModule\Models;

use App\GatewayModule\Exceptions\ChpasswdErrorException;
use App\GatewayModule\Models\RootManager;
use Tester\Assert;
use Tests\Stubs\CoreModule\Models\Command;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * tests for Root manager
 */
final class RootManagerTest extends CommandTestCase {

	/**
	 * Password change command
	 */
	private const COMMAND = 'chpasswd';

	/**
	 * Root user and password to change
	 */
	private const ARGUMENT = 'root:testpass';

	/**
	 * @var RootManager Root manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new RootManager($this->commandManager);
	}

	/**
	 * Tests the function to set root password
	 */
	public function testSetRootpass(): void {
		$command = new Command(self::COMMAND, '', '', 0);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMAND, true, self::ARGUMENT])
			->andReturn($command);
		Assert::noError(function (): void {
			$this->manager->setPassword('testpass');
		});
	}

	/**
	 * Tests the fuction to set root password with failure
	 */
	public function testSetRootpassError(): void {
		$command = new Command(self::COMMAND, '', '', 1);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMAND, true, self::ARGUMENT])
			->andReturn($command);
		Assert::throws(function (): void {
			$this->manager->setPassword('testpass');
		}, ChpasswdErrorException::class);
	}

}

$test = new RootManagerTest();
$test->run();
