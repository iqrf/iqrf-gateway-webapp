<?php

/**
 * TEST: App\Models\Database\Entities\User
 * @covers App\Models\Database\Entities\User
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\Models\Database\Entities;

use App\Exceptions\InvalidUserLanguageException;
use App\Exceptions\InvalidUserRoleException;
use App\Models\Database\Entities\User;
use Nette\Security\Identity;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for user database entity
 */
class UserTest extends TestCase {

	/**
	 * @var string User name
	 */
	private $username = 'admin';

	/**
	 * @var string Password
	 */
	private $password = 'iqrf';

	/**
	 * @var string User role
	 */
	private $role = User::ROLE_POWER;

	/**
	 * @var string User language
	 */
	private $language = User::LANGUAGE_ENGLISH;

	/**
	 * @var User User entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->entity = new User($this->username, $this->password, $this->role, $this->language);
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
		Assert::same($this->username, $this->entity->getUserName());
	}

	/**
	 * Tests the function to get the user's password hash
	 */
	public function testGetPassword(): void {
		$hash = $this->entity->getPassword();
		Assert::true(password_verify($this->password, $hash));
	}

	/**
	 * Tests the function to get the user's role
	 */
	public function testGetRole(): void {
		Assert::same($this->role, $this->entity->getRole());
	}

	/**
	 * Tests the function to get the user's language
	 */
	public function testGetLanguage(): void {
		Assert::same($this->language, $this->entity->getLanguage());
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
		Assert::true($this->entity->verifyPassword($this->password));
		Assert::false($this->entity->verifyPassword('admin'));
	}

	/**
	 * Tests the function to return JSON serialized entity
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'id' => null,
			'username' => $this->username,
			'role' => $this->role,
			'language' => $this->language,
		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

	/**
	 * Tests the function to convert the entity into Nette Identity
	 */
	public function testToIdentity(): void {
		$data = [
			'username' => $this->username,
			'language' => $this->language,
		];
		$expected = new Identity(null, [$this->role], $data);
		$actual = $this->entity->toIdentity();
		Assert::equal($expected, $actual);
		Assert::same([$this->role], $actual->getRoles());
		Assert::null($actual->getId());
	}

}

$test = new UserTest();
$test->run();
