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

use App\CoreModule\Models\UserManager;
use App\Models\Database\Entities\User;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CLI command for user management
 */
class UserAddCommand extends Command {

	/**
	 * @var string|null Command name
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected static $defaultName = 'user:add';

	/**
	 * @var UserRepository User database repository
	 */
	protected UserRepository $repository;

	/**
	 * @var EntityManager Entity manager
	 */
	protected EntityManager $entityManager;

	/**
	 * @var UserManager User manager
	 */
	protected UserManager $userManager;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 * @param UserManager $userManager User manager
	 */
	public function __construct(EntityManager $entityManager, UserManager $userManager) {
		parent::__construct();
		$this->entityManager = $entityManager;
		$this->repository = $entityManager->getUserRepository();
		$this->userManager = $userManager;
	}

	/**
	 * Configures the user add command
	 */
	protected function configure(): void {
		$this->setDescription('Adds webapp\'s user');
		$definitions = [
			new InputOption('username', ['u', 'user'], InputOption::VALUE_OPTIONAL, 'Username of the new user'),
			new InputOption('password', ['p', 'pass'], InputOption::VALUE_OPTIONAL, 'New user\'s password'),
			new InputOption('role', ['r', 'role'], InputOption::VALUE_OPTIONAL, 'New user\'s role'),
			new InputOption('language', ['l', 'lang'], InputOption::VALUE_OPTIONAL, 'New user\'s language'),
		];
		$this->setDefinition(new InputDefinition($definitions));
	}

	/**
	 * Executes the user add command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$style = new SymfonyStyle($input, $output);
		$name = $this->askUserName($input, $output);
		$pass = $this->askPassword($input, $output);
		$role = $this->askRole($input, $output);
		$lang = $this->askLanguage($input, $output);
		if ($this->userManager->checkUsernameUniqueness($name)) {
			$style->error('The specified username is already taken.');
			return 1;
		}
		$user = new User($name, null, $pass, $role, $lang);
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		return 0;
	}

	/**
	 * Asks for the username
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return string Username
	 */
	private function askUserName(InputInterface $input, OutputInterface $output): string {
		$username = $input->getOption('username');
		while ($username === null) {
			$helper = $this->getHelper('question');
			$question = new Question('Please enter the username: ');
			$name = $helper->ask($input, $output, $question);
			if ($name !== null && $this->repository->findOneByUserName($name) === null) {
				$username = $name;
			} else {
				$output->writeln('This username is already taken. Please choose another username.');
			}
		}
		return $username;
	}

	/**
	 * Asks for the user's password
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return string User's password
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

	/**
	 * Asks for the user's role
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return string User's role
	 */
	private function askRole(InputInterface $input, OutputInterface $output): string {
		$role = $input->getOption('role');
		while ($role === null || !in_array($role, User::ROLES, true)) {
			$helper = $this->getHelper('question');
			$question = new ChoiceQuestion('Please enter the user\'s role: ', User::ROLES, User::ROLE_DEFAULT);
			$role = $helper->ask($input, $output, $question);
		}
		return $role;
	}

	/**
	 * Asks for the user's language
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return string User's language
	 */
	private function askLanguage(InputInterface $input, OutputInterface $output): string {
		$language = $input->getOption('language');
		while ($language === null || !in_array($language, User::LANGUAGES, true)) {
			$helper = $this->getHelper('question');
			$question = new ChoiceQuestion('Please enter the user\'s language: ', User::LANGUAGES, User::LANGUAGE_DEFAULT);
			$language = $helper->ask($input, $output, $question);
		}
		return $language;
	}

}
