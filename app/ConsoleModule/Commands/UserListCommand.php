<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
use Nette\Utils\Json;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI command for user management
 */
#[AsCommand(name: 'user:list', description: 'Lists webapp\'s users')]
class UserListCommand extends UserCommand {

	/**
	 * Configures the user list command
	 */
	protected function configure(): void {
		$definitions = [
			new InputOption('output-json', ['J', 'output-json'], InputOption::VALUE_NONE, 'Output as JSON.'),
		];
		$this->setDefinition(new InputDefinition($definitions));
	}

	/**
	 * Executes the user list command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$json = ($input->getOption('output-json') !== false);
		$users = $this->getUsers();
		if ($json) {
			$output->writeln(Json::encode($users));
		} else {
			$header = ['ID', 'Username', 'Email', 'Role', 'Language', 'State'];
			$table = new Table($output);
			$table->setHeaders($header);
			$table->setRows($users);
			$table->render();
		}
		return 0;
	}

	/**
	 * Returns all registered users
	 * @return array<int, array<string, int|string>> Registered users
	 */
	private function getUsers(): array {
		return array_map(static fn (User $user): array => $user->jsonSerialize(), $this->repository->findAll());
	}

}
