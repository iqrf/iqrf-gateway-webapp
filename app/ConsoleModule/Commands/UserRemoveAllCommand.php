<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

use App\Exceptions\InvalidUserRoleException;
use App\Models\Database\Entities\User;
use App\Models\Database\Enums\UserRole;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CLI command for removal of all users
 */
#[AsCommand(name: 'user:remove-all', description: 'Removes all webapp users')]
class UserRemoveAllCommand extends UserCommand {

	/**
	 * Configures the user remove all command
	 */
	protected function configure(): void {
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
		try {
			$role = $this->askUsersRole($input, $output);
		} catch (InvalidUserRoleException $e) {
			$style->error('Role ' . $input->getOption('role') . ' does not exist.');
			return 1;
		}
		$criteria = $role === null ? [] : ['role' => $role];
		$users = $this->repository->findBy($criteria);
		if ($input->isInteractive() && $users !== []) {
			$helper = $this->getHelper('question');
			$question = new ConfirmationQuestion(sprintf('Do you really want to remove user(s) %s? (y/N)', $this->usersToString($users)), false);
			if (!$helper->ask($input, $output, $question)) {
				$style->warning('No users were removed.');
				return 0;
			}
		}
		foreach ($users as $user) {
			$this->entityManager->remove($user);
		}
		$this->entityManager->flush();
		$this->printUsers($users, $style);
		return 0;
	}

	/**
	 * Asks for role to remove users by
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return UserRole|null Role
	 */
	protected function askUsersRole(InputInterface $input, OutputInterface $output): ?UserRole {
		$role = $input->getOption('role');
		if (!$input->isInteractive()) {
			return $role !== null ? UserRole::fromString($role) : null;
		}
		$roles = array_column(UserRole::cases(), 'value');
		while ($role === null) {
			$helper = $this->getHelper('question');
			$question = new ConfirmationQuestion('Do you want to filter removed users by role? (y/N)', false);
			if (!$helper->ask($input, $output, $question)) {
				return null;
			}
			$helper = $this->getHelper('question');
			$question = new ChoiceQuestion('Please select a role to delete users by: ', $roles);
			$role = UserRole::tryFrom($helper->ask($input, $output, $question));
		}
		return $role;
	}

	/**
	 * Converts array of users to a string of usernames
	 * @param array<User> $users Array of users
	 * @return string|null String of usernames
	 */
	private function usersToString(array $users): ?string {
		if ($users === []) {
			return null;
		}
		return implode(', ', array_map(static fn (User $user): string => $user->getUserName(), $users));
	}

	/**
	 * Prints styled user remove success message
	 * @param array<User> $users Array of users
	 * @param SymfonyStyle $style Symfony style
	 */
	private function printUsers(array $users, SymfonyStyle $style): void {
		if ($users === []) {
			$style->success('There are no users with that role to remove.');
			return;
		}
		if (count($users) === 1) {
			$style->success('User ' . $this->usersToString($users) . ' was successfully removed.');
			return;
		}
		$style->success('Users ' . $this->usersToString($users) . ' were successfully removed.');
	}

}
