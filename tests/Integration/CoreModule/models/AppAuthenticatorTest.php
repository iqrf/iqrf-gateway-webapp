<?php

/**
 * TEST: App\CoreModule\Models\AppAuthenticator
 * @covers App\CoreModule\Models\AppAuthenticator
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Integration\CoreModule\Model;

use App\CoreModule\Models\AppAuthenticator;
use Nette\Security\AuthenticationException;
use Nette\Security\Identity;
use Tester\Assert;
use Tester\Environment;
use Tests\Toolkit\TestCases\DatabaseTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for application authenticator manager
 */
class AppAuthenticatorTest extends DatabaseTestCase {

	/**
	 * @var AppAuthenticator User manager
	 */
	private $authenticator;

	/**
	 * @var mixed[string] Information about admin user
	 */
	private $data = [
		'id' => 1,
		'username' => 'admin',
		'password' => '$2y$10$FtORaX9VfUOCNFIvm1xrBuxnQqHdMZm1/x5DqhtuR77fG72jsqX3y',
		'role' => 'power',
		'language' => 'en',
	];

	/**
	 * Tests the function to authenticate the user (incorrect username)
	 */
	public function testAuthenticateBadUsername(): void {
		Assert::exception(function (): void {
			$credentials = ['iqrf', 'iqrf'];
			$this->authenticator->authenticate($credentials);
		}, AuthenticationException::class);
	}

	/**
	 * Tests the function to authenticate the user (incorrect password)
	 */
	public function testAuthenticateBadPassword(): void {
		Assert::exception(function (): void {
			$credentials = ['admin', 'admin'];
			$this->authenticator->authenticate($credentials);
		}, AuthenticationException::class);
	}

	/**
	 * Tests the function to authenticate the user (correct username and password)
	 */
	public function testAuthenticateSuccess(): void {
		$data = ['username' => 'admin', 'language' => 'en'];
		$expected = new Identity(1, 'power', $data);
		$credentials = ['admin', 'iqrf'];
		Assert::equal($expected, $this->authenticator->authenticate($credentials));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		Environment::lock('user_db', __DIR__ . '/../../../../temp/');
		parent::setUp();
		$this->createTable();
		$this->authenticator = new AppAuthenticator($this->context);
	}

	/**
	 * Creates the database table
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

$test = new AppAuthenticatorTest();
$test->run();
