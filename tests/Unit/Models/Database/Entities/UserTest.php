<?php

/**
 * TEST: App\Models\Database\Entities\User
 * @covers App\Models\Database\Entities\User
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

use App\Exceptions\InvalidEmailAddressException;
use App\Exceptions\InvalidPasswordException;
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
	 * @var string User name
	 */
	private const USERNAME = 'admin';

	/**
	 * @var string E-mail address
	 */
	private const EMAIL = 'admin@iqrf.org';

	/**
	 * @var string Password
	 */
	private const PASSWORD = 'iqrf';

	/**
	 * @var string User role
	 */
	private const ROLE = User::ROLE_ADMIN;

	/**
	 * @var string User language
	 */
	private const LANGUAGE = User::LANGUAGE_ENGLISH;

	/**
	 * @var int User account state
	 */
	private const STATE = User::STATE_UNVERIFIED;

	/**
	 * @var User User entity
	 */
	private User $entity;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->entity = new User(self::USERNAME, self::EMAIL, self::PASSWORD, self::ROLE, self::LANGUAGE);
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
	 * Tests the function to get the user's email address
	 */
	public function testGetEmail(): void {
		Assert::same(self::EMAIL, $this->entity->getEmail());
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
	 * Tests the function to set the user's email address (remove e-mail address)
	 */
	public function testSetEmailNull(): void {
		$this->entity->setEmail(null);
		Assert::null($this->entity->getEmail());
	}

	/**
	 * Tests the function to set the user's email address (remove e-mail address)
	 */
	public function testSetEmailEmptyString(): void {
		$this->entity->setEmail('');
		Assert::null($this->entity->getEmail());
	}

	/**
	 * Tests the function to set the user's email address (valid e-mail address)
	 */
	public function testSetEmailValid(): void {
		$email = 'test@iqrf.org';
		$this->entity->setEmail($email);
		Assert::same($email, $this->entity->getEmail());
	}

	/**
	 * Tests the function to set the user's email address (invalid e-mail address)
	 */
	public function testSetEmailInvalid(): void {
		Assert::throws(function (): void {
			$this->entity->setEmail('example.com');
		}, InvalidEmailAddressException::class, 'No domain part found' . PHP_EOL . 'Domain accepts no mail (Null MX, RFC7505)');
	}

	/**
	 * Tests the function to set the user's email address (missing MX DNS record)
	 */
	public function testSetEmailMissingMx(): void {
		Assert::throws(function (): void {
			$this->entity->setEmail('admin@example.com');
		}, InvalidEmailAddressException::class, 'Domain accepts no mail (Null MX, RFC7505)');
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
	 * Tests the function to set the user's password (empty string)
	 */
	public function testSetPasswordEmptyString(): void {
		Assert::throws(function (): void {
			$this->entity->setPassword('');
		}, InvalidPasswordException::class);
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
		Assert::same(User::ROLE_ADMIN, $this->entity->getRole());
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
			'email' => self::EMAIL,
			'role' => self::ROLE,
			'language' => self::LANGUAGE,
			'state' => User::STATES[self::STATE],
		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

}

$test = new UserTest();
$test->run();
