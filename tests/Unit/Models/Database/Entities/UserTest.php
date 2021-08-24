<?php

/**
 * TEST: App\Models\Database\Entities\User
 * @covers App\Models\Database\Entities\User
 * @phpVersion >= 7.3
 * @testCase
 */
/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

namespace Tests\Unit\Models\Database\Entities;

use App\Exceptions\InvalidUserLanguageException;
use App\Exceptions\InvalidUserRoleException;
use App\Models\Database\Entities\User;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for user database entity
 */
final class UserTest extends TestCase {

	/**
	 * User name
	 */
	private const USERNAME = 'admin';

	/**
	 * Password
	 */
	private const PASSWORD = 'iqrf';

	/**
	 * User role
	 */
	private const ROLE = User::ROLE_POWER;

	/**
	 * User language
	 */
	private const LANGUAGE = User::LANGUAGE_ENGLISH;

	/**
	 * User account state
	 */
	private const STATE = User::STATE_UNVERIFIED;

	/**
	 * @var User User entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->entity = new User(self::USERNAME, null, self::PASSWORD, self::ROLE, self::LANGUAGE);
	}

	/**
	 * Tests the function to get the user's ID
	 */
	public function testGetId(): void {
		Assert::null($this->entity->getId());
	}

	/**
	 * Tests the function to get the user name
	 */
	public function testGetUserName(): void {
		Assert::same(self::USERNAME, $this->entity->getUserName());
	}

	/**
	 * Tests the function to get the user's password hash
	 */
	public function testGetPassword(): void {
		$hash = $this->entity->getPassword();
		Assert::true(password_verify(self::PASSWORD, $hash));
	}

	/**
	 * Tests the function to get the user's role
	 */
	public function testGetRole(): void {
		Assert::same(self::ROLE, $this->entity->getRole());
	}

	/**
	 * Tests the function to get the user's language
	 */
	public function testGetLanguage(): void {
		Assert::same(self::LANGUAGE, $this->entity->getLanguage());
	}

	/**
	 * Tests the function to set the username
	 */
	public function testSetUserName(): void {
		$username = 'iqrf';
		$this->entity->setUserName($username);
		Assert::same($username, $this->entity->getUserName());
	}

	/**
	 * Tests the function to set the user's password
	 */
	public function testSetPassword(): void {
		$password = 'admin';
		$this->entity->setPassword($password);
		Assert::true($this->entity->verifyPassword($password));
	}

	/**
	 * Tests the function to set the user's role
	 */
	public function testSetRole(): void {
		$role = User::ROLE_NORMAL;
		$this->entity->setRole($role);
		Assert::same($role, $this->entity->getRole());
	}

	/**
	 * Tests the function to set the user's role
	 */
	public function testSetRoleInvalid(): void {
		Assert::exception(function (): void {
			$this->entity->setRole('invalid');
		}, InvalidUserRoleException::class);
		Assert::same(User::ROLE_POWER, $this->entity->getRole());
	}

	/**
	 * Tests the function to set the user's language
	 */
	public function testSetLanguage(): void {
		$language = User::LANGUAGE_ENGLISH;
		$this->entity->setLanguage($language);
		Assert::same($language, $this->entity->getLanguage());
	}

	/**
	 * Tests the function to set the user's language
	 */
	public function testSetLanguageInvalid(): void {
		Assert::exception(function (): void {
			$this->entity->setLanguage('invalid');
		}, InvalidUserLanguageException::class);
		Assert::same(User::LANGUAGE_DEFAULT, $this->entity->getLanguage());
	}

	/**
	 * Tests the function to verify the user's password
	 */
	public function testVerifyPassword(): void {
		Assert::true($this->entity->verifyPassword(self::PASSWORD));
		Assert::false($this->entity->verifyPassword('admin'));
	}

	/**
	 * Tests the function to return JSON serialized entity
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'id' => null,
			'username' => self::USERNAME,
			'email' => null,
			'role' => self::ROLE,
			'language' => self::LANGUAGE,
			'state' => User::STATES[self::STATE],
		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

}

$test = new UserTest();
$test->run();
