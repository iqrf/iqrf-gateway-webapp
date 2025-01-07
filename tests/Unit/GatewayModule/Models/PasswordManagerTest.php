<?php

/**
 * TEST: App\GatewayModule\Models\PasswordManager
 * @covers App\GatewayModule\Models\PasswordManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

namespace Tests\Unit\GatewayModule\Models;

use App\CoreModule\Models\FeatureManager;
use App\GatewayModule\Exceptions\ChpasswdErrorException;
use App\GatewayModule\Models\PasswordManager;
use Iqrf\CommandExecutor\Tester\Traits\CommandExecutorTestCase;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * tests for Gateway user password manager
 */
final class PasswordManagerTest extends TestCase {

	use CommandExecutorTestCase;

	/**
	 * Password change command
	 */
	private const COMMAND = 'chpasswd';

	/**
	 * Root user and password to change
	 */
	private const ARGUMENT = 'root:testpass';

	/**
	 * @var PasswordManager Root manager
	 */
	private PasswordManager $manager;

	/**
	 * Tests the function to change gateway user password
	 */
	public function testSetPassword(): void {
		$this->receiveCommand(
			command: self::COMMAND,
			needSudo: true,
			timeout: 60,
			input: self::ARGUMENT,
		);
		Assert::noError(function (): void {
			$this->manager->setPassword('testpass');
		});
	}

	/**
	 * Tests the function to change gateway user password with change error
	 */
	public function testSetPasswordChangeError(): void {
		$this->receiveCommand(
			command: self::COMMAND,
			needSudo: true,
			timeout: 60,
			input: self::ARGUMENT,
			exitCode: 1,
		);
		Assert::throws(function (): void {
			$this->manager->setPassword('testpass');
		}, ChpasswdErrorException::class);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->setUpCommandExecutor();
		$original = __DIR__ . '/../../../data/features.neon';
		$path = TMP_DIR . '/features.neon';
		FileSystem::copy($original, $path);
		$this->manager = new PasswordManager($this->commandExecutor, new FeatureManager($path));
	}

}

$test = new PasswordManagerTest();
$test->run();
