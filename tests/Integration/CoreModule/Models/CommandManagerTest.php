<?php

/**
 * TEST: App\CoreModule\Models\CommandManager
 * @covers App\CoreModule\Models\CommandManager
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Integration\CoreModule\Models;

use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use Nette\Utils\Strings;
use Symfony\Component\Process\Process;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for command manager
 */
final class CommandManagerTest extends TestCase {

	/**
	 * Executed command
	 */
	private const COMMAND = 'echo "OK"';

	/**
	 * @var CommandManager Command manager
	 */
	private $manager;

	/**
	 * Tests the function to execute a shell command
	 */
	public function testRun(): void {
		$actual = $this->manager->run(self::COMMAND);
		Assert::same(self::COMMAND, $actual->getCommand());
		Assert::same('OK', $actual->getStdout());
		Assert::same('', $actual->getStderr());
		Assert::same(0, $actual->getExitCode());
	}

	/**
	 * Tests the function to execute a shell command asynchronously
	 */
	public function testRunAsync(): void {
		$this->manager->runAsync(function (string $type, ?string $buffer): void {
			Assert::same(Process::OUT, $type);
			Assert::same('OK', Strings::trim($buffer));
		}, self::COMMAND, false, 10);
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
		$commandStack = new CommandStack();
		$this->manager = new CommandManager(false, $commandStack);
	}

}

$test = new CommandManagerTest();
$test->run();
