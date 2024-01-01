<?php

/**
 * TEST: App\InstallModule\Models\SudoManager
 * @covers App\InstallModule\Models\SudoManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

	/**
	 * Command
	 */
	private const COMMAND = 'sudo -v';

	/**
	 * @var SudoManager Sudo manager
	 */
	private SudoManager $manager;

	/**
	 * Tests the function to check sudo and if webapp can use sudo
	 */
	public function testCheckSudo(): void {
		Environment::lock('sudo_check', TMP_DIR);
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

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new SudoManager($this->commandManager);
	}

}

$test = new SudoManagerTest();
$test->run();
