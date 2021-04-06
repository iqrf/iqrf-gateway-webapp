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
		Environment::lock('sudo_check', __DIR__ . '/../../../temp/');
		$command = new Command(self::COMMAND, '', '', 0);
		$this->commandManager->shouldReceive('commandExist')
			->withArgs(['sudo'])
			->andReturn(true);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMAND])
			->andReturn($command);
		$userId = posix_geteuid();
		$groupId = posix_getegid();
		$username = posix_getpwuid($userId)['name'];
		if ($username === 'root') {
			$username = 'www-data';
			$user_info = posix_getpwnam($username);
			posix_seteuid($user_info['uid']);
			posix_setegid($user_info['gid']);
		}
		$expected = [
			'user' => $username,
			'exists' => true,
			'userSudo' => true,
		];
		Assert::same($expected, $this->manager->checkSudo());
		posix_seteuid($userId);
		posix_setegid($groupId);
	}

	/**
	 * Tests the function to check sudo and if webapp can use sudo with root
	 */
	public function testCheckSudoRoot(): void {
		$username = posix_getpwuid(posix_geteuid())['name'];
		if ($username !== 'root') {
			Environment::skip('This test has to be run under root.');
		}
		Assert::same([], $this->manager->checkSudo());
	}

}

$test = new SudoManagerTest();
$test->run();
