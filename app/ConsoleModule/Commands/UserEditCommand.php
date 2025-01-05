<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * CLI command for user management
 */
class UserEditCommand extends UserCommand {

	/**
	 * @var string|null Command name
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected static $defaultName = 'user:edit';

	/**
	 * Configures the user edit command
	 */
	protected function configure(): void {
		$this->setDescription('Edits webapp\'s user');
		$definitions = [
			new InputOption('username', ['u', 'user'], InputOption::VALUE_OPTIONAL, 'Username of the edited user'),
			new InputOption('role', ['r', 'role'], InputOption::VALUE_OPTIONAL, 'New user\'s role'),
			new InputOption('language', ['l', 'lang'], InputOption::VALUE_OPTIONAL, 'New user\'s language'),
		];
		$this->setDefinition(new InputDefinition($definitions));
	}

	/**
	 * Executes the user edit command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$user = $this->askUserName($input, $output);
		$role = $this->askRole($user, $input, $output);
		$language = $this->askLanguage($user, $input, $output);
		$user->setRole($role);
		$user->setLanguage($language);
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		return 0;
	}

	/**
	 * Asks for the user's role
	 * @param User $user Information about the user
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return string New user's role
	 */
	private function askRole(User $user, InputInterface $input, OutputInterface $output): string {
		$role = $input->getOption('role');
		while ($role === null || !in_array($role, User::ROLES, true)) {
			$helper = $this->getHelper('question');
			$question = new ChoiceQuestion('Please enter the user\'s role: ', User::ROLES, $user->getRole());
			$role = $helper->ask($input, $output, $question);
		}
		return $role;
	}

	/**
	 * Asks for the user's language
	 * @param User $user Information about the user
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return string New user's language
	 */
	private function askLanguage(User $user, InputInterface $input, OutputInterface $output): string {
		$language = $input->getOption('language');
		while ($language === null || !in_array($language, User::LANGUAGES, true)) {
			$helper = $this->getHelper('question');
			$question = new ChoiceQuestion('Please enter the user\'s language: ', User::LANGUAGES, $user->getLanguage());
			$language = $helper->ask($input, $output, $question);
		}
		return $language;
	}

}
