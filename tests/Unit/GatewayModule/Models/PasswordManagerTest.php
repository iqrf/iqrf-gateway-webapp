<?php

/**
 * TEST: App\GatewayModule\Models\PasswordManager
 * @covers App\GatewayModule\Models\PasswordManager
 * @phpVersion >= 7.3
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\GatewayModule\Models;

use App\CoreModule\Models\FeatureManager;
use App\GatewayModule\Exceptions\ChpasswdErrorException;
use App\GatewayModule\Models\PasswordManager;
use Nette\Utils\FileSystem;
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
	private const COMMAND = 'chpasswd';

	/**
	 * Root user and password to change
	 */
	private const ARGUMENT = 'root:testpass';

	/**
	 * Path to the temporary directory
	 */
	private const TMP_PATH = __DIR__ . '/../../../../temp/tests';

	/**
	 * @var PasswordManager Root manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$original = __DIR__ . '/../../../data/features.neon';
		$path = self::TMP_PATH . '/features.neon';
		FileSystem::copy($original, $path);
		$this->manager = new PasswordManager($this->commandManager, new FeatureManager($path));
	}

	/**
	 * Tests the function to change gateway user password
	 */
	public function testSetPassword(): void {
		$command = new Command(self::COMMAND, '', '', 0);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMAND, true, 60, self::ARGUMENT])
			->andReturn($command);
		Assert::noError(function (): void {
			$this->manager->setPassword('testpass');
		});
	}

	/**
	 * Tests the fuction to change gateway user password with change error
	 */
	public function testSetPasswordChangeError(): void {
		$command = new Command(self::COMMAND, '', '', 1);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMAND, true, 60, self::ARGUMENT])
			->andReturn($command);
		Assert::throws(function (): void {
			$this->manager->setPassword('testpass');
		}, ChpasswdErrorException::class);
	}

}

$test = new PasswordManagerTest();
$test->run();
