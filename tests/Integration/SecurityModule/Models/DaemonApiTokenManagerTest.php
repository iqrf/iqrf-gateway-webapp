<?php

/**
 * TEST: App\SecurityModule\Models\DaemonApiTokenManager
 * @covers App\SecurityModule\Models\DaemonApiTokenManager
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

use App\SecurityModule\Exceptions\DaemonApiTokenManagerException;
use App\SecurityModule\Exceptions\DaemonApiTokenNotFoundException;
use App\SecurityModule\Exceptions\DaemonApiTokenNotValidException;
use App\SecurityModule\Models\DaemonApiTokenManager;
use Iqrf\CommandExecutor\Tester\Traits\CommandExecutorTestCase;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Daemon API token manager class
 */
final class DaemonApiTokenManagerTest extends TestCase {

	use CommandExecutorTestCase;

	/**
	 * List tokens command
	 */
	private const LIST_COMMAND = DaemonApiTokenManager::COMMAND . ' list -j';

	/**
	 * Get token command
	 */
	private const GET_COMMAND = DaemonApiTokenManager::COMMAND . ' get -j -i 1';

	/**
	 * Get missing token command
	 */
	private const GET_COMMAND_MISSING = DaemonApiTokenManager::COMMAND . ' get -j -i 2';

	/**
	 * Create token with relative expiration command
	 */
	private const CREATE_COMMAND_RELATIVE = DaemonApiTokenManager::COMMAND . ' create -o \'test\' -e 30d -j';

	/**
	 * Create token with absolute expiration command
	 */
	private const CREATE_COMMAND_ABSOLUTE = DaemonApiTokenManager::COMMAND . ' create -o \'test\' -e 2030-01-01T10:00:00Z -j';

	/**
	 * Revoke token command
	 */
	private const REVOKE_COMMAND = DaemonApiTokenManager::COMMAND . ' revoke -i 1';

	/**
	 * Rotate token command
	 */
	private const ROTATE_TOKEN = DaemonApiTokenManager::COMMAND . ' rotate -j -i 1';

	/**
	 * @param DaemonApiTokenManager Daemon API token manager
	 */
	private DaemonApiTokenManager $manager;

	/**
	 * Tests the function to list tokens with no records available
	 */
	public function testListTokensEmpty(): void {
		$this->receiveCommandExist(
			command: DaemonApiTokenManager::COMMAND,
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
			actual: $this->manager->listTokens(),
		);
	}

	/**
	 * Tests the function to list tokens with records available
	 */
	public function testListTokensRecords(): void {
		$this->receiveCommandExist(
			command: DaemonApiTokenManager::COMMAND,
			output: true,
		);
		$expected = '[{"created_at":"2026-02-20T12:00:00Z","expires_at":"2026-02-20T12:00:00Z","id":1,"invalidated_at":null,"owner":"test","service":false,"status":0}]';
		$this->receiveCommand(
			command: self::LIST_COMMAND,
			needSudo: true,
			stdout: $expected,
			exitCode: 0,
		);
		Assert::same(
			expected: $expected,
			actual: $this->manager->listTokens(),
		);
	}

	/**
	 * Tests the function to list tokens with internal error ocurring
	 */
	public function testListTokensInternalError(): void {
		$this->receiveCommandExist(
			command: DaemonApiTokenManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::LIST_COMMAND,
			needSudo: true,
			exitCode: 1,
		);
		Assert::throws(function (): void {
			$this->manager->listTokens();
		}, DaemonApiTokenManagerException::class);
	}

	/**
	 * Tests the function to get token (exists)
	 */
	public function testGetToken(): void {
		$this->receiveCommandExist(
			command: DaemonApiTokenManager::COMMAND,
			output: true,
		);
		$expected = '{"created_at":"2026-02-20T12:00:00Z","expires_at":"2026-02-20T12:00:00Z","id":1,"invalidated_at":null,"owner":"test","service":false,"status":0}';
		$this->receiveCommand(
			command: self::GET_COMMAND,
			needSudo: true,
			stdout: $expected,
			exitCode: 0,
		);
		Assert::same(
			expected: $expected,
			actual: $this->manager->getToken(1),
		);
	}

	/**
	 * Tests the function to get token (nonexistent)
	 */
	public function testGetTokenNonexistent(): void {
		$this->receiveCommandExist(
			command: DaemonApiTokenManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::GET_COMMAND_MISSING,
			needSudo: true,
			exitCode: 3,
		);
		Assert::throws(function (): void {
			$this->manager->getToken(2);
		}, DaemonApiTokenNotFoundException::class);
	}

	/**
	 * Tests the function to get token with internal error occurring
	 */
	public function testGetTokenInternalError(): void {
		$this->receiveCommandExist(
			command: DaemonApiTokenManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::GET_COMMAND,
			needSudo: true,
			exitCode: 1,
		);
		Assert::throws(function (): void {
			$this->manager->getToken(1);
		}, DaemonApiTokenManagerException::class);
	}

	/**
	 * Tests the function to create token with relative expiration
	 */
	public function testCreateTokenRelative(): void {
		$this->receiveCommandExist(
			command: DaemonApiTokenManager::COMMAND,
			output: true,
		);
		$expected = [
			'id' => 1,
			'token' => 'iqrfgd2;1;zDrcvQaXWopzJ+DbfkpGq3Tn00wkt3n6fExj8iUsYio=',
		];
		$this->receiveCommand(
			command: self::CREATE_COMMAND_RELATIVE,
			needSudo: true,
			stdout: '{"id":1,"token":"iqrfgd2;1;zDrcvQaXWopzJ+DbfkpGq3Tn00wkt3n6fExj8iUsYio="}',
			exitCode: 0,
		);
		Assert::same(
			expected: $expected,
			actual: $this->manager->createToken(
				(object) [
					'owner' => 'test',
					'unit' => 'd',
					'count' => 30,
				],
			)
		);
	}

	/**
	 * Tests the function to create token with absolute expiration
	 */
	public function testCreateTokenAbsolute(): void {
		$this->receiveCommandExist(
			command: DaemonApiTokenManager::COMMAND,
			output: true,
		);
		$expected = [
			'id' => 1,
			'token' => 'iqrfgd2;1;zDrcvQaXWopzJ+DbfkpGq3Tn00wkt3n6fExj8iUsYio=',
		];
		$this->receiveCommand(
			command: self::CREATE_COMMAND_ABSOLUTE,
			needSudo: true,
			stdout: '{"id":1,"token":"iqrfgd2;1;zDrcvQaXWopzJ+DbfkpGq3Tn00wkt3n6fExj8iUsYio="}',
			exitCode: 0,
		);
		Assert::same(
			expected: $expected,
			actual: $this->manager->createToken(
				(object) [
					'owner' => 'test',
					'expiration' => '2030-01-01T10:00:00Z',
				],
			)
		);
	}

	/**
	 * Tests the function to create token with internal error occurring
	 */
	public function testCreateTokenInternalError(): void {
		$this->receiveCommandExist(
			command: DaemonApiTokenManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::CREATE_COMMAND_ABSOLUTE,
			needSudo: true,
			exitCode: 1,
		);
		Assert::throws(function (): void {
			$this->manager->createToken(
				(object) [
					'owner' => 'test',
					'expiration' => '2030-01-01T10:00:00Z',
				],
			);
		}, DaemonApiTokenManagerException::class);
	}

	/**
	 * Tests the function to revoke token
	 */
	public function testRevokeToken(): void {
		$this->receiveCommandExist(
			command: DaemonApiTokenManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::REVOKE_COMMAND,
			needSudo: true,
			exitCode: 0,
		);
		Assert::noError(function (): void {
			$this->manager->revokeToken(1);
		});
	}

	/**
	 * Tests the function to revoke token that is expired
	 */
	public function testRevokeTokenExpired(): void {
		$this->receiveCommandExist(
			command: DaemonApiTokenManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::REVOKE_COMMAND,
			needSudo: true,
			exitCode: 4,
		);
		Assert::noError(function (): void {
			$this->manager->revokeToken(1);
		});
	}

	/**
	 * Tests the function to revoke token that is already revoked
	 */
	public function testRevokeTokenRevoked(): void {
		$this->receiveCommandExist(
			command: DaemonApiTokenManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::REVOKE_COMMAND,
			needSudo: true,
			exitCode: 5,
		);
		Assert::noError(function (): void {
			$this->manager->revokeToken(1);
		});
	}

	/**
	 * Tests the function to revoke token (nonexistent)
	 */
	public function testRevokeTokenNoRecord(): void {
		$this->receiveCommandExist(
			command: DaemonApiTokenManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::REVOKE_COMMAND,
			needSudo: true,
			exitCode: 3,
		);
		Assert::throws(function (): void {
			$this->manager->revokeToken(1);
		}, DaemonApiTokenNotFoundException::class);
	}

	/**
	 * Tests the function to revoke token with internal error
	 */
	public function testRevokeTokenInternalError(): void {
		$this->receiveCommandExist(
			command: DaemonApiTokenManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::REVOKE_COMMAND,
			needSudo: true,
			exitCode: 1,
		);
		Assert::throws(function (): void {
			$this->manager->revokeToken(1);
		}, DaemonApiTokenManagerException::class);
	}

	/**
	 * Tests the function to rotate token
	 */
	public function testRotateToken(): void {
		$this->receiveCommandExist(
			command: DaemonApiTokenManager::COMMAND,
			output: true,
		);
		$expected = [
			'id' => 2,
			'token' => 'iqrfgd2;2;uiJCz+dKPZIaa1bn8vq3xcmktblBsoyoWz0UBpU8Cx8=',
		];
		$this->receiveCommand(
			command: self::ROTATE_TOKEN,
			needSudo: true,
			stdout: '{"id":2,"token":"iqrfgd2;2;uiJCz+dKPZIaa1bn8vq3xcmktblBsoyoWz0UBpU8Cx8="}',
			exitCode: 0,
		);
		Assert::same(
			expected: $expected,
			actual: $this->manager->rotateToken(1),
		);
	}

	/**
	 * Tests the function to rotate nonexistent token
	 */
	public function testRotateTokenNonexistent(): void {
		$this->receiveCommandExist(
			command: DaemonApiTokenManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::ROTATE_TOKEN,
			needSudo: true,
			exitCode: 3,
		);
		Assert::throws(function (): void {
			$this->manager->rotateToken(1);
		}, DaemonApiTokenNotFoundException::class);
	}

	/**
	 * Tests the function to rotate token that is already expired
	 */
	public function testRotateTokenExpired(): void {
		$this->receiveCommandExist(
			command: DaemonApiTokenManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::ROTATE_TOKEN,
			needSudo: true,
			exitCode: 4,
		);
		Assert::throws(function (): void {
			$this->manager->rotateToken(1);
		}, DaemonApiTokenNotValidException::class);
	}

	/**
	 * Tests the function to rotate token that is already revoked
	 */
	public function testRotateTokenRevoked(): void {
		$this->receiveCommandExist(
			command: DaemonApiTokenManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::ROTATE_TOKEN,
			needSudo: true,
			exitCode: 5,
		);
		Assert::throws(function (): void {
			$this->manager->rotateToken(1);
		}, DaemonApiTokenNotValidException::class);
	}

	/**
	 * Tests the function to rotate token that with internal error ocurring
	 */
	public function testRotateTokenInternalError(): void {
		$this->receiveCommandExist(
			command: DaemonApiTokenManager::COMMAND,
			output: true,
		);
		$this->receiveCommand(
			command: self::ROTATE_TOKEN,
			needSudo: true,
			exitCode: 1,
		);
		Assert::throws(function (): void {
			$this->manager->rotateToken(1);
		}, DaemonApiTokenManagerException::class);
	}

	/**
	 * Tests the function to check whether utility is available {exists}
	 */
	public function testCheckUtilityExists(): void {
		$this->receiveCommandExist(
			command: DaemonApiTokenManager::COMMAND,
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
			command: DaemonApiTokenManager::COMMAND,
			output: false,
		);
		Assert::throws(function (): void {
			$this->manager->checkUtility();
		}, DaemonApiTokenManagerException::class);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->setUpCommandExecutor();
		$this->manager = new DaemonApiTokenManager($this->commandExecutor);
	}

}

$test = new DaemonApiTokenManagerTest();
$test->run();
