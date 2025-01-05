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
	 * @var string Password change command
	 */
	private const COMMAND = 'chpasswd';

	/**
	 * @var string Root user and password to change
	 */
	private const ARGUMENT = 'root:testpass';

	/**
	 * @var PasswordManager Root manager
	 */
	private PasswordManager $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$original = __DIR__ . '/../../../data/features.neon';
		$path = TMP_DIR . '/features.neon';
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
