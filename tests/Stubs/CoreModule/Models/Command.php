<?php

declare(strict_types = 1);

namespace Tests\Stubs\CoreModule\Models;

use App\CoreModule\Entities\ICommand;

/**
 * Command entity stub
 */
final class Command implements ICommand {

	/**
	 * @var string Command
	 */
	private $command;

	/**
	 * @var string Standard output
	 */
	private $stdout;

	/**
	 * @var string Standard error output
	 */
	private $stderr;

	/**
	 * @var int|null Exit code
	 */
	private $exitCode;

	/**
	 * Constructor
	 * @param string $command Command
	 * @param string $stdout Standard output
	 * @param string $stderr Standard error output
	 * @param int $exitCode Exit code
	 */
	public function __construct(string $command, string $stdout, string $stderr, int $exitCode) {
		$this->command = $command;
		$this->stdout = $stdout;
		$this->stderr = $stderr;
		$this->exitCode = $exitCode;
	}

	/**
	 * Returns the command
	 * @return string Command
	 */
	public function getCommand(): string {
		return $this->command;
	}

	/**
	 * Returns the standard output
	 * @return string Standard output
	 */
	public function getStdout(): string {
		return $this->stdout;
	}

	/**
	 * Returns the standard error output
	 * @return string Standard error output
	 */
	public function getStderr(): string {
		return $this->stderr;
	}

	/**
	 * Returns the exit code
	 * @return int|null Exit code
	 */
	public function getExitCode(): ?int {
		return $this->exitCode;
	}

}
