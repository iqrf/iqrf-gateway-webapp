<?php

/**
 * TEST: App\InstallModule\Models\SudoManager
 * @covers App\InstallModule\Models\SudoManager
 * @phpVersion >= 7.3
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\InstallModule\Models;

use App\InstallModule\Models\SudoManager;
use Tester\Assert;
use Tester\Environment;
use Tests\Stubs\CoreModule\Models\Command;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Sudo manager
 */
final class SudoManagerTest extends CommandTestCase {

	private const COMMAND = 'sudo -v';

	/**
	 * @var SudoManager Sudo manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new SudoManager($this->commandManager);
	}

	/**
	 * Tests the function to check sudo and if webapp can use sudo
	 */
	public function testCheckSudo(): void {
		Environment::lock();
		$command = new Command(self::COMMAND, '', '', 0);
		$this->commandManager->shouldReceive('commandExist')
			->withArgs(['sudo'])
			->andReturn(true);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMAND])
			->andReturn($command);
		$user_info = posix_getpwnam('www-data');
		posix_seteuid($user_info['uid']);
		posix_setegid($user_info['gid']);
		$expected = [
			'user' => 'www-data',
			'exists' => true,
			'userSudo' => true,
		];
		Assert::same($expected, $this->manager->checkSudo());
		posix_seteuid(0);
		posix_setegid(0);
	}

	/**
	 * Tests the function to check sudo and if webapp can use sudo with root
	 */
	public function testCheckSudoRoot(): void {
		Assert::same([], $this->manager->checkSudo());
	}

}

$test = new SudoManagerTest();
$test->run();
