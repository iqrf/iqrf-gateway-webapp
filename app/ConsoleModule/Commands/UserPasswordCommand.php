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

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * CLI command for user management
 */
class UserPasswordCommand extends UserCommand {

	/**
	 * @var string|null Command name
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected static $defaultName = 'user:password';

	/**
	 * Configures the change user's password command
	 */
	protected function configure(): void {
		$this->setDescription('Change webapp\'s user\'s password');
		$definitions = [
			new InputOption('username', ['u', 'user'], InputOption::VALUE_OPTIONAL, 'Username of the edited user'),
			new InputOption('password', ['p', 'pass'], InputOption::VALUE_OPTIONAL, 'New user\'s password'),
		];
		$this->setDefinition(new InputDefinition($definitions));
	}

	/**
	 * Executes the change user's password command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$user = $this->askUserName($input, $output);
		$password = $this->askPassword($input, $output);
		$user->setPassword($password);
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		return 0;
	}

	/**
	 * Asks for the user's password
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return string New user's password
	 */
	private function askPassword(InputInterface $input, OutputInterface $output): string {
		$password = $input->getOption('password');
		while ($password === null) {
			$helper = $this->getHelper('question');
			$question = new Question('Please enter the new user\'s password: ');
			$password = $helper->ask($input, $output, $question);
		}
		return $password;
	}

}
