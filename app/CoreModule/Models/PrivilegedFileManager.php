<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

namespace App\CoreModule\Models;

use Nette\IOException;

/**
 * Privileged file manager
 */
class PrivilegedFileManager implements IFileManager {

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var string Directory path
	 */
	private $directory;

	/**
	 * Constructor
	 * @param string $directory Directory path
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(string $directory, CommandManager $commandManager) {
		$this->commandManager = $commandManager;
		$this->directory = $directory;
	}

	/**
	 * Reads the file
	 * @param string $fileName File name
	 * @return string File content
	 * @throws IOException
	 */
	public function read(string $fileName): string {
		$command = $this->commandManager->run('cat ' . $this->directory . '/' . $fileName, true);
		if ($command->getExitCode() !== 0) {
			throw new IOException($command->getStderr());
		}
		return $command->getStdout();
	}

	/**
	 * Deletes the file
	 * @param string $fileName File name
	 * @throws IOException
	 */
	public function delete(string $fileName): void {
		$command = $this->commandManager->run('rm -rf ' . $this->directory . '/' . $fileName, true);
		if ($command->getExitCode() !== 0) {
			throw new IOException($command->getStderr());
		}
	}

	/**
	 * Writes into the file
	 * @param string $fileName File name
	 * @param mixed $content File content
	 * @throws IOException
	 */
	public function write(string $fileName, $content): void {
		$command = $this->commandManager->run('mkdir -p ' . $this->directory . '/' . dirname($fileName), true);
		if ($command->getExitCode() !== 0) {
			throw new IOException($command->getStderr());
		}
		$command = $this->commandManager->run('tee ' . $this->directory . '/' . $fileName, true, 60, $content);
		if ($command->getExitCode() !== 0) {
			throw new IOException($command->getStderr());
		}
	}

	/**
	 * Returns list of files in directory
	 * @return array<int, string> List of files
	 */
	public function listFiles(): array {
		$command = $this->commandManager->run('find ' . $this->directory);
		if ($command->getExitCode() !== 0) {
			throw new IOException($command->getStderr());
		}
		return explode(PHP_EOL, $command->getStdout());
	}

}
