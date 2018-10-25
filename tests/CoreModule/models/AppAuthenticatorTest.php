<?php

/**
 * TEST: App\CoreModule\Models\AppAuthenticator
 * @covers App\CoreModule\Models\AppAuthenticator
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\CoreModule\Model;

use App\CoreModule\Models\AppAuthenticator;
use Nette\Caching\Storages\MemoryStorage;
use Nette\Database\Connection;
use Nette\Database\Context;
use Nette\Database\Structure;
use Nette\DI\Container;
use Nette\Security\AuthenticationException;
use Nette\Security\Identity;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for certificate manager
 */
class AppAuthenticatorTest extends TestCase {

	/**
	 * @var AppAuthenticator User manager
	 */
	private $authenticator;

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var Context Nette Database context
	 */
	private $context;

	/**
	 * @var array Information about admin user
	 */
	private $data = [
		'id' => 1,
		'username' => 'admin',
		'password' => '$2y$10$FtORaX9VfUOCNFIvm1xrBuxnQqHdMZm1/x5DqhtuR77fG72jsqX3y',
		'role' => 'power',
		'language' => 'en',
	];

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Test function to authenticate the user (incorrect username)
	 */
	public function testAuthenticateBadUsername(): void {
		Assert::exception(function () {
			$credentials = ['iqrf', 'iqrf'];
			$this->authenticator->authenticate($credentials);
		}, AuthenticationException::class);
	}

	/**
	 * Test function to authenticate the user (incorrect password)
	 */
	public function testAuthenticateBadPassword(): void {
		Assert::exception(function () {
			$credentials = ['admin', 'admin'];
			$this->authenticator->authenticate($credentials);
		}, AuthenticationException::class);
	}

	/**
	 * Test function to authenticate the user (correct username and password)
	 */
	public function testAuthenticateSuccess(): void {
		$data = ['username' => 'admin', 'language' => 'en',];
		$expected = new Identity(1, 'power', $data);
		$credentials = ['admin', 'iqrf'];
		Assert::equal($expected, $this->authenticator->authenticate($credentials));
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		\Tester\Environment::lock('user_db', __DIR__ . '/../../temp/');
		$connection = new Connection('sqlite::memory:');
		$cacheStorage = new MemoryStorage();
		$structure = new Structure($connection, $cacheStorage);
		$this->context = new Context($connection, $structure);
		$this->createTable();
		$this->authenticator = new AppAuthenticator($this->context);
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
		$this->context->table('users')->insert($this->data);
	}

}

$test = new AppAuthenticatorTest($container);
$test->run();
