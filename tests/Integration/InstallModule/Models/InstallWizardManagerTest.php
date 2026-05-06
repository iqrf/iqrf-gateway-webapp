<?php

/**
 * TEST: App\InstallModule\Models\InstallWizardManager
 * @covers App\InstallModule\Models\InstallWizardManager
 * @phpVersion >= 8.2
 * @testCase
 */
/**
 * Copyright 2023-2026 IQRF Tech s.r.o.
 * Copyright 2023-2026 MICRORISC s.r.o.
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

namespace Tests\Cases\Integration\InstallModule\Models;

use App\InstallModule\Exceptions\FactoryPasswordNotConfiguredException;
use App\InstallModule\Models\InstallWizardManager;
use Iqrf\CommandExecutor\Tester\Traits\CommandExecutorTestCase;
use Iqrf\FileManager\FileManager;
use Mockery;
use Mockery\MockInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for install wizard manager
 */
final class InstallWizardManagerTest extends TestCase {

	use CommandExecutorTestCase;

	/**
	 * Input (plain) password
	 */
	private const PLAIN_PASSWORD = 'testpass';

	/**
	 * Hashed password
	 */
	private const HASHED_PASSWORD = '$argon2id$v=19$m=65536,t=3,p=1$ZHpsMjhwcWMvNU9sV1RpYQ$Exj6XjBB6yeQtyPHw8LUzqXnoFlB5WXaeJTnpHFiQAg';

	/**
	 * @var FileManager|MockInterface Mocked file manager
	 */
	private FileManager|MockInterface $fileManager;

	/**
	 * @var InstallWizardManager Install wizard manager
	 */
	private InstallWizardManager $manager;

	/**
	 * Tests the function to verify access to installation wizard
	 */
	public function testVerifyAccess(): void {
		$this->fileManager->shouldReceive('exists')
			->withArgs([InstallWizardManager::PASSWORD_FILE])
			->andReturn(true);
		$this->fileManager->shouldReceive('read')
			->withArgs([InstallWizardManager::PASSWORD_FILE])
			->andReturn(self::HASHED_PASSWORD);
		Assert::true($this->manager->verifyAccess(self::PLAIN_PASSWORD));
	}

	/**
	 * Tests the function to verify access to installation wizard with invalid password
	 */
	public function testVerifyAccessInvalidPassword(): void {
		$this->fileManager->shouldReceive('exists')
			->withArgs([InstallWizardManager::PASSWORD_FILE])
			->andReturn(true);
		$this->fileManager->shouldReceive('read')
			->withArgs([InstallWizardManager::PASSWORD_FILE])
			->andReturn(self::HASHED_PASSWORD);
		Assert::false($this->manager->verifyAccess('differentpassword'));
	}

	public function testVerifyAccessEmptyFile(): void {
		$this->fileManager->shouldReceive('exists')
			->withArgs([InstallWizardManager::PASSWORD_FILE])
			->andReturn(true);
		$this->fileManager->shouldReceive('read')
			->withArgs([InstallWizardManager::PASSWORD_FILE])
			->andReturn('');
		Assert::throws(function (): void {
			$this->manager->verifyAccess(self::PLAIN_PASSWORD);
		}, FactoryPasswordNotConfiguredException::class);
	}

	/**
	 * Tests the function to verify access to installation wizard with password file missing
	 */
	public function testVerifyAccessMissingFile(): void {
		$this->fileManager->shouldReceive('exists')
			->withArgs([InstallWizardManager::PASSWORD_FILE])
			->andReturn(false);
		Assert::throws(function (): void {
			$this->manager->verifyAccess(self::PLAIN_PASSWORD);
		}, FactoryPasswordNotConfiguredException::class);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->fileManager = Mockery::mock(FileManager::class);
		$this->manager = new InstallWizardManager($this->fileManager);
	}

}

(new InstallWizardManagerTest())->run();
