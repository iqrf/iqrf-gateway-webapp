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

namespace App\ConsoleModule\Commands;

use App\Models\Database\Entities\User;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CLI command for removal of all users
 */
class UserRemoveAllCommand extends UserCommand {

	/**
	 * @var string Command name
	 */
	protected static $defaultName = 'user:remove-all';

	/**
	 * Configures the user remove all command
	 */
	protected function configure(): void {
		$this->setDescription('Removes all webapp users');
		$definitions = [
			new InputOption('role', ['r'], InputOption::VALUE_OPTIONAL, 'Only remove all users with a specific role.'),
		];
		$this->setDefinition($definitions);
	}

	/**
	 * Executes the user remove all command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$style = new SymfonyStyle($input, $output);
		$style->title('Remove all users');
		$role = $this->askRole($input, $output);
		$users = [];
		if ($role === 'all') {
			$users = $this->repository->findAll();
		} else {
			$users = $this->repository->findBy(['role' => $role]);
		}
		$helper = $this->getHelper('question');
		$usernames = implode(', ', array_map(function (User $user): string {
			return $user->getUserName();
		}, $users));
		if (strlen($usernames) !== 0) {
			$usernames = ' [' . $usernames . ']';
		}
		$question = new ConfirmationQuestion('Do you really want to remove all selected users?' . $usernames, false);
		if (!$helper->ask($input, $output, $question)) {
			return 0;
		}
		foreach ($users as $user) {
			assert($user instanceof User);
			$this->entityManager->remove($user);
			$this->entityManager->flush();
		}
		return 0;
	}

	/**
	 * Asks for role to remove users by
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return string|null Role
	 */
	private function askRole(InputInterface $input, OutputInterface $output): ?string {
		$role = $input->getOption('role');
		if (!$input->isInteractive()) {
			return $role ?? 'all';
		}
		$roles = array_merge(['all'], User::ROLES);
		while ($role === null || !in_array($role, $roles, true)) {
			$helper = $this->getHelper('question');
			$question = new ChoiceQuestion('Please select a role to delete users by: ', $roles);
			$role = $helper->ask($input, $output, $question);
		}
		return $role;
	}

}
