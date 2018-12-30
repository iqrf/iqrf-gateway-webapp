<?php

/**
 * TEST: App\CoreModule\Models\UserManager
 * @covers App\CoreModule\Models\UserManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\CoreModule\Model;

use App\CoreModule\Exceptions\InvalidPasswordException;
use App\CoreModule\Exceptions\UsernameAlreadyExistsException;
use App\CoreModule\Models\UserManager;
use Nette\Caching\Storages\MemoryStorage;
use Nette\Database\Connection;
use Nette\Database\Context;
use Nette\Database\Structure;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for certificate manager
 */
class UserManagerTest extends TestCase {

	/**
	 * @var Context Nette Database context
	 */
	private $context;

	/**
	 * @var string[] Information about the user admin
	 */
	private $data = [
		'username' => 'admin',
		'password' => '$2y$10$FtORaX9VfUOCNFIvm1xrBuxnQqHdMZm1/x5DqhtuR77fG72jsqX3y',
		'role' => 'power',
		'language' => 'en',
	];

	/**
	 * @var UserManager User manager
	 */
	private $manager;

	/**
	 * Test function to change the password (incorrect old password)
	 */
	public function testChangePasswordFail(): void {
		$this->createUser();
		Assert::exception(function (): void {
			$this->manager->changePassword(1, 'admin', 'admin');
		}, InvalidPasswordException::class);
	}

	/**
	 * Create a power user
	 */
	private function createUser(): void {
		$this->context->table('users')->insert($this->data);
	}

	/**
	 * Test function to change the password (correct old password)
	 */
	public function testChangePasswordSuccess(): void {
		$this->createUser();
		$password = 'admin';
		$this->manager->changePassword(1, 'iqrf', $password);
		$hash = $this->context->table('users')->fetch()->toArray()['password'];
		Assert::true(password_verify($password, $hash));
	}

	/**
	 * Test function to delete the user
	 */
	public function testDelete(): void {
		$this->createUser();
		Assert::same(1, $this->context->table('users')->count());
		$this->manager->delete(1);
		Assert::same(0, $this->context->table('users')->count());
	}

	/**
	 * Test function to edit the user (fail)
	 */
	public function testEditFail(): void {
		$this->createUser();
		$user = $this->data;
		$user['username'] = 'user';
		$this->context->table('users')->insert($user);
		Assert::exception(function (): void {
			$this->manager->edit(2, 'admin', 'normal', 'cs');
		}, UsernameAlreadyExistsException::class);
	}

	/**
	 * Test function to edit the user (success)
	 */
	public function testEditSuccess(): void {
		$this->createUser();
		$this->manager->edit(1, 'admin', 'normal', 'cs');
		$actual = $this->context->table('users')->fetch()->toArray();
		unset($actual['password']);
		$expected = [
			'id' => 1,
			'username' => 'admin',
			'role' => 'normal',
			'language' => 'cs',
		];
		Assert::same($expected, $actual);
	}

	/**
	 * Test function to get information about the user (fail)
	 */
	public function testGetInfoFail(): void {
		Assert::null($this->manager->getInfo(1));
	}

	/**
	 * Test function to get information about the user (success)
	 */
	public function testGetInfoSuccess(): void {
		$this->createUser();
		$actual = $this->manager->getInfo(1);
		unset($actual['id']);
		Assert::same($this->data, $actual);
	}

	/**
	 * Test function to get all registered users (no registered user)
	 */
	public function testGetUsersNone(): void {
		Assert::same([], $this->manager->getUsers());
	}

	/**
	 * Test function to get all registered users (a registered user)
	 */
	public function testGetUsersOne(): void {
		$this->createUser();
		$expected = [['id' => 1]];
		$expected[0] += $this->data;
		Assert::same($expected, $this->manager->getUsers());
	}

	/**
	 * Test function to register a new user (fail)
	 */
	public function testRegisterFail(): void {
		$this->createUser();
		Assert::exception(function (): void {
			$this->manager->register('admin', 'iqrf', 'power', 'en');
		}, UsernameAlreadyExistsException::class);
	}

	/**
	 * Test function to register a new user (success)
	 */
	public function testRegisterSuccess(): void {
		$this->manager->register('admin', 'iqrf', 'power', 'en');
		$expected = $this->data;
		$actual = $this->context->table('users')->fetch()->toArray();
		$hash = $actual['password'];
		unset($actual['id'], $actual['password'], $expected['password']);
		Assert::same($expected, $actual);
		Assert::true(password_verify('iqrf', $hash));
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		Environment::lock('user_db', __DIR__ . '/../../temp/');
		$connection = new Connection('sqlite::memory:');
		$cacheStorage = new MemoryStorage();
		$structure = new Structure($connection, $cacheStorage);
		$this->context = new Context($connection, $structure);
		$this->createTable();
		$this->manager = new UserManager($this->context);
	}

	/**
	 * Create database table
	 */
	private function createTable(): void {
		$sql = 'CREATE TABLE `users` (
	`id`		INTEGER PRIMARY KEY AUTOINCREMENT,
	`username`	TEXT NOT NULL UNIQUE,
	`password`	TEXT NOT NULL,
	`role`		TEXT NOT NULL,
	`language`	TEXT DEFAULT \'en\'
);';
		$this->context->query($sql);
	}

}

$test = new UserManagerTest();
$test->run();
