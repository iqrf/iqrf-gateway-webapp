<?php

/**
 * TEST: App\GatewayModule\Models\PasswordManager
 * @covers App\GatewayModule\Models\PasswordManager
 * @phpVersion >= 7.3
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\GatewayModule\Models;

use App\GatewayModule\Exceptions\ChpasswdErrorException;
use App\GatewayModule\Models\PasswordManager;
use Tester\Assert;
use Tests\Stubs\CoreModule\Models\Command;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * tests for Gateway user password manager
 */
final class PasswordManagerTest extends CommandTestCase {

	/**
	 * Password change command
	 */
	private const COMMANDS = [
		'getDistro' => 'cat /etc/os-release | grep -e "^ID="',
		'changePassword' => 'chpasswd',
	];

	/**
	 * Root user and password to change
	 */
	private const ARGUMENT = 'root:testpass';

	/**
	 * @var PasswordManager Root manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new PasswordManager($this->commandManager);
	}

	/**
	 * Tests the function to change gateway user password
	 */
	public function testSetPassword(): void {
		$osCommand = new Command(self::COMMANDS['getDistro'], 'ID=ubuntu', '', 0);
		$chpasswdCommand = new Command(self::COMMANDS['changePassword'], '', '', 0);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['getDistro']])
			->andReturn($osCommand);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['changePassword'], true, self::ARGUMENT])
			->andReturn($chpasswdCommand);
		Assert::noError(function (): void {
			$this->manager->setPassword('testpass');
		});
	}

	/**
	 * Tests the function to change gateway user password with distro fetch error
	 */
	public function testSetPasswordOsError(): void {
		$osCommand = new Command(self::COMMANDS['getDistro'], '', '', 1);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['getDistro']])
			->andReturn($osCommand);
		Assert::throws(function (): void {
			$this->manager->setPassword('testpass');
		}, ChpasswdErrorException::class);
	}

	/**
	 * Tests the fuction to change gateway user password with change error
	 */
	public function testSetPasswordChangeError(): void {
		$osCommand = new Command(self::COMMANDS['getDistro'], 'ID=ubuntu', '', 0);
		$chpasswdCommand = new Command(self::COMMANDS['changePassword'], '', '', 1);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['getDistro']])
			->andReturn($osCommand);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['changePassword'], true, self::ARGUMENT])
			->andReturn($chpasswdCommand);
		Assert::throws(function (): void {
			$this->manager->setPassword('testpass');
		}, ChpasswdErrorException::class);
	}

}

$test = new PasswordManagerTest();
$test->run();
