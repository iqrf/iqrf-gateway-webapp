<?php

declare(strict_types = 1);

namespace Tests\Toolkit\TestCases;

use App\CoreModule\Models\CommandManager;
use Mockery;
use Mockery\MockInterface;
use Tester\TestCase;
use Tests\Stubs\CoreModule\Models\Command;

/**
 * Shell command test case
 */
abstract class CommandTestCase extends TestCase {

	/**
	 * @var CommandManager|MockInterface Mocked command manager
	 */
	protected $commandManager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->commandManager = Mockery::mock(CommandManager::class);
	}

	/**
	 * Cleanups the test environment
	 */
	protected function tearDown(): void {
		Mockery::close();
	}

	/**
	 * Receives a command
	 * @param string $command Command
	 * @param bool $needSudo Is sudo needed?
	 * @param string $stdout Command's standard output
	 * @param string $stderr Command's standard error output
	 * @param int $exitCode Command's exit code
	 */
	protected function receiveCommand(string $command, ?bool $needSudo = null, string $stdout = '', string $stderr = '', int $exitCode = 0): void {
		$process = $this->commandManager->shouldReceive('run');
		$entity = new Command($needSudo ? 'sudo ' : '' . $command, $stdout, $stderr, $exitCode);
		if ($needSudo === null) {
			$process->with($command);
		} else {
			$process->with($command, $needSudo);
		}
		$process->andReturn($entity);
	}

	/**
	 * Receives a command existence check
	 * @param string $command Command
	 * @param bool $output Is the command exist?
	 */
	protected function receiveCommandExist(string $command, bool $output): void {
		$this->commandManager->shouldReceive('commandExist')
			->with($command)->andReturn($output);
	}

	/**
	 * Receives an async command
	 * @param callable $callback Command's callback
	 * @param string $command Command
	 * @param bool|null $needSudo Is sudo needed?
	 */
	protected function receiveAsyncCommand(callable $callback, string $command, ?bool $needSudo = null): void {
		$process = $this->commandManager->shouldReceive('runAsync');
		if ($needSudo === null) {
			$process->with($callback, $command);
		} else {
			$process->with($callback, $command, $needSudo);
		}
	}

}
