<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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

namespace App\ConsoleModule\Commands;

use App\Models\Database\Entities\User;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI command for user management
 */
class UserListCommand extends UserCommand {

	/**
	 * @var string Command name
	 */
	protected static $defaultName = 'user:list';

	/**
	 * Configures the user list command
	 */
	protected function configure(): void {
		$this->setDescription('Lists webapp\'s users');
	}

	/**
	 * Executes the user list command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$header = ['ID', 'Username', 'Role', 'Language'];
		$table = new Table($output);
		$table->setHeaders($header);
		$table->setRows($this->getUsers());
		$table->render();
		return 0;
	}

	/**
	 * Returns all registered users
	 * @return array<int, array<string, int|string>> Registered users
	 */
	private function getUsers(): array {
		$users = [];
		foreach ($this->repository->findAll() as $user) {
			assert($user instanceof User);
			$users[] = $user->jsonSerialize();
		}
		return $users;
	}

}
