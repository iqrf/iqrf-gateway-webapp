<?php

/**
 * TEST: App\CoreModule\Models\CommandManager
 * @covers App\CoreModule\Models\CommandManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Integration\CoreModule\Models;

use App\CoreModule\Models\CommandManager;
use Nette\Utils\Strings;
use Symfony\Component\Process\Process;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for command manager
 */
class CommandManagerTest extends TestCase {

	/**
	 * @var CommandManager Command manager
	 */
	private $manager;

	/**
	 * Tests the function to execute a shell command
	 */
	public function testRun(): void {
		Assert::same('OK', $this->manager->run('echo "OK"'));
	}

	/**
	 * Tests the function to execute a shell command asynchronously
	 */
	public function testRunAsync(): void {
		$this->manager->runAsync(function (string $type, ?string $buffer): void {
			Assert::same(Process::OUT, $type);
			Assert::same('OK', Strings::trim($buffer));
		}, 'echo "OK"', false, 10);
	}

	/**
	 * Tests the function to check the existence of a command (fail)
	 */
	public function testCommandExistFail(): void {
		Assert::false($this->manager->commandExist('sndikasdhisdbajdbas'));
	}

	/**
	 * Tests the function to check the existence of a command (success)
	 */
	public function testCommandExistSuccess(): void {
		Assert::true($this->manager->commandExist('echo'));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->manager = new CommandManager(false);
	}

}

$test = new CommandManagerTest();
$test->run();
