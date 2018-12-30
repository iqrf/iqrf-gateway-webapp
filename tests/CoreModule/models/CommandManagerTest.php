<?php

/**
 * TEST: App\CoreModule\Models\CommandManager
 * @covers App\CoreModule\Models\CommandManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\CoreModule\Model;

use App\CoreModule\Models\CommandManager;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for command manager
 */
class CommandManagerTest extends TestCase {

	/**
	 * @var CommandManager Command manager
	 */
	private $manager;

	/**
	 * Test function to execute a shell command
	 */
	public function testSend(): void {
		Assert::same('OK', $this->manager->send('echo "OK"'));
	}

	/**
	 * Test function to check the existence of a command (fail)
	 */
	public function testCommandExistFail(): void {
		Assert::false($this->manager->commandExist('sndikasdhisdbajdbas'));
	}

	/**
	 * Test function to check the existence of a command (success)
	 */
	public function testCommandExistSuccess(): void {
		Assert::true($this->manager->commandExist('echo'));
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$this->manager = new CommandManager(false);
	}

}

$test = new CommandManagerTest();
$test->run();
