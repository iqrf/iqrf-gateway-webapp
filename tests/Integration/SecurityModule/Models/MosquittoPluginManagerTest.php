<?php

/**
 * TEST: App\SecurityModule\Models\MosquittoPluginManager
 * @covers App\SecurityModule\Models\MosquittoPluginManager
 * @phpVersion >= 8.2
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

namespace Tests\Integration\SecurityModule\Models;

use App\SecurityModule\Enums\MosquittoPluginManagerStatusCodes;
use App\SecurityModule\Exceptions\MosquittoPluginManagerException;
use App\SecurityModule\Exceptions\MosquittoPluginManagerInvalidParamsException;
use App\SecurityModule\Exceptions\MosquittoPluginUserNotFoundException;
use App\SecurityModule\Models\MosquittoPluginManager;
use Iqrf\CommandExecutor\Tester\Traits\CommandExecutorTestCase;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Mosquitto plugin manager class
 */
final class MosquittoPluginManagerTest extends TestCase {

	use CommandExecutorTestCase;

	/**
	 * List mosquitto users command
	 */
	private const LIST_COMMAND = MosquittoPluginManager::COMMAND . ' list -j';

	/**
	 * Get mosquitto user command
	 */
	private const GET_COMMAND = MosquittoPluginManager::COMMAND . ' get -j -i 1';

	/**
	 * Get missing mosquitto user command
	 */
	private const GET_COMMAND_MISSING = MosquittoPluginManager::COMMAND . ' get -j -i 2';

	/**
	 * Create mosquitto user command
	 */
	private const CREATE_COMMAND = MosquittoPluginManager::COMMAND . ' create -u \'usertest\' -p \'testpass123456789\' -j';

	/**
	 * Create mosquitto user with invalid password command
	 */
	private const CREATE_COMMAND_INVALID_PASSWORD = MosquittoPluginManager::COMMAND . ' create -u \'usertest\' -p \'1234\' -j';

	/**
	 * Block mosquitto user command
	 */
	private const BLOCK_COMMAND = MosquittoPluginManager::COMMAND . ' block -i 1';

	/**
	 * @param MosquittoPluginManager Daemon API token manager
	 */
	private MosquittoPluginManager $manager;

	/**
	 * Tests the function to list users with no records available
	 */
	public function testListUsersEmpty(): void {
		$this->receiveCommandExist(
			command: MosquittoPluginManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::LIST_COMMAND,
			needSudo: true,
			stdout: '[]',
			exitCode: 0,
		);
		Assert::same(
			expected: '[]',
			actual: $this->manager->listUsers(),
		);
	}

	/**
	 * Tests the function to list users with records available
	 */
	public function testListUsersRecords(): void {
		$this->receiveCommandExist(
			command: MosquittoPluginManager::COMMAND,
			output: true,
		);
		$expected = '[{"blockedAt":null,"createdAt":"2026-03-20T19:29:28.112Z","id":3,"state":0,"username":"usertest"}]';
		$this->receiveCommand(
			command: self::LIST_COMMAND,
			needSudo: true,
			stdout: $expected,
			exitCode: 0,
		);
		Assert::same(
			expected: $expected,
			actual: $this->manager->listUsers(),
		);
	}

	/**
	 * Tests the function to list users with internal error ocurring
	 */
	public function testListUsersInternalError(): void {
		$this->receiveCommandExist(
			command: MosquittoPluginManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::LIST_COMMAND,
			needSudo: true,
			exitCode: 1,
		);
		Assert::throws(function (): void {
			$this->manager->listUsers();
		}, MosquittoPluginManagerException::class);
	}

	/**
	 * Tests the function to get user (exists)
	 */
	public function testGetUser(): void {
		$this->receiveCommandExist(
			command: MosquittoPluginManager::COMMAND,
			output: true,
		);
		$expected = '{"blockedAt":null,"createdAt":"2026-03-20T19:29:28.112Z","id":3,"state":0,"username":"usertest"}';
		$this->receiveCommand(
			command: self::GET_COMMAND,
			needSudo: true,
			stdout: $expected,
			exitCode: 0,
		);
		Assert::same(
			expected: $expected,
			actual: $this->manager->getUser(1),
		);
	}

	/**
	 * Tests the function to get user (nonexistent)
	 */
	public function testGetUserNonexistent(): void {
		$this->receiveCommandExist(
			command: MosquittoPluginManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::GET_COMMAND_MISSING,
			needSudo: true,
			exitCode: MosquittoPluginManagerStatusCodes::USER_NOT_FOUND->value,
		);
		Assert::throws(function (): void {
			$this->manager->getUser(2);
		}, MosquittoPluginUserNotFoundException::class);
	}

	/**
	 * Tests the function to get user with internal error occurring
	 */
	public function testGetUserInternalError(): void {
		$this->receiveCommandExist(
			command: MosquittoPluginManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::GET_COMMAND,
			needSudo: true,
			exitCode: 1,
		);
		Assert::throws(function (): void {
			$this->manager->getUser(1);
		}, MosquittoPluginManagerException::class);
	}

	/**
	 * Tests the function to create user
	 */
	public function testCreateUser(): void {
		$this->receiveCommandExist(
			command: MosquittoPluginManager::COMMAND,
			output: true,
		);
		$expected = [
			'id' => 1,
			'blockedAt' => null,
			'username' => 'usertest',
			'createdAt' => '2026-03-20T19:29:28.112Z',
			'state' => 0,
		];
		$this->receiveCommand(
			command: self::CREATE_COMMAND,
			needSudo: true,
			stdout: '{"blockedAt":null,"createdAt":"2026-03-20T19:29:28.112Z","id":1,"state":0,"username":"usertest"}',
			exitCode: 0,
		);
		Assert::equal(
			expected: $expected,
			actual: $this->manager->createUser(
				(object) [
					'username' => 'usertest',
					'password' => 'testpass123456789',
				],
			)
		);
	}

	/**
	 * Tests the function to create user with invalid parameters
	 */
	public function testCreateUserInvalidParams(): void {
		$this->receiveCommandExist(
			command: MosquittoPluginManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::CREATE_COMMAND_INVALID_PASSWORD,
			needSudo: true,
			exitCode: 3,
		);
		Assert::throws(function (): void {
			$this->manager->createUser(
				(object) [
					'username' => 'usertest',
					'password' => '1234',
				],
			);
		}, MosquittoPluginManagerInvalidParamsException::class);
	}

	/**
	 * Tests the function to create user with internal error occurring
	 */
	public function testCreateUserInternalError(): void {
		$this->receiveCommandExist(
			command: MosquittoPluginManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::CREATE_COMMAND,
			needSudo: true,
			exitCode: 1,
		);
		Assert::throws(function (): void {
			$this->manager->createUser(
				(object) [
					'username' => 'usertest',
					'password' => 'testpass123456789',
				],
			);
		}, MosquittoPluginManagerException::class);
	}

	/**
	 * Tests the function to block user
	 */
	public function testBlockUser(): void {
		$this->receiveCommandExist(
			command: MosquittoPluginManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::BLOCK_COMMAND,
			needSudo: true,
			exitCode: 0,
		);
		Assert::noError(function (): void {
			$this->manager->blockUser(1);
		});
	}

	/**
	 * Tests the function to block nonexistent user
	 */
	public function testBlockUserMissing(): void {
		$this->receiveCommandExist(
			command: MosquittoPluginManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::BLOCK_COMMAND,
			needSudo: true,
			exitCode: MosquittoPluginManagerStatusCodes::USER_NOT_FOUND->value,
		);
		Assert::throws(function (): void {
			$this->manager->blockUser(1);
		}, MosquittoPluginUserNotFoundException::class);
	}

	/**
	 * Tests the function to block user that is already blocked
	 */
	public function testBlockUserBlocked(): void {
		$this->receiveCommandExist(
			command: MosquittoPluginManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::BLOCK_COMMAND,
			needSudo: true,
			exitCode: MosquittoPluginManagerStatusCodes::USER_BLOCKED->value,
		);
		Assert::noError(function (): void {
			$this->manager->blockUser(1);
		});
	}

	/**
	 * Tests the function to block user with internal error
	 */
	public function testBlockUserInternalError(): void {
		$this->receiveCommandExist(
			command: MosquittoPluginManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::BLOCK_COMMAND,
			needSudo: true,
			exitCode: 1,
		);
		Assert::throws(function (): void {
			$this->manager->blockUser(1);
		}, MosquittoPluginManagerException::class);
	}

	/**
	 * Tests the function to check whether utility is available {exists}
	 */
	public function testCheckUtilityExists(): void {
		$this->receiveCommandExist(
			command: MosquittoPluginManager::COMMAND,
			output: true,
		);
		Assert::noError(function (): void {
			$this->manager->checkUtility();
		});
	}

	/**
	 * Tests the function to check whether utility is available {does not exist}
	 */
	public function testCheckUtlityNotExists(): void {
		$this->receiveCommandExist(
			command: MosquittoPluginManager::COMMAND,
			output: false,
		);
		Assert::throws(function (): void {
			$this->manager->checkUtility();
		}, MosquittoPluginManagerException::class);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->setUpCommandExecutor();
		$this->manager = new MosquittoPluginManager($this->commandExecutor);
	}

}

$test = new MosquittoPluginManagerTest();
$test->run();
