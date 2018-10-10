<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
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

use App\ConsoleModule\Model\ConsoleUserManager;
use Nette\SmartObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * CLI command for user management
 */
class UserAddCommand extends Command {

	use SmartObject;

	/**
	 * @var string Command name
	 */
	protected static $defaultName = 'user:add';

	/**
	 * @var ConsoleUserManager User manager
	 */
	protected $userManager;

	/**
	 * Constructor
	 * @param ConsoleUserManager $userManager User manager
	 */
	public function __construct(ConsoleUserManager $userManager) {
		parent::__construct();
		$this->userManager = $userManager;
	}

	/**
	 * Configure the command
	 */
	protected function configure(): void {
		$this->setName('user:add');
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
	 * Execute the command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 */
	protected function execute(InputInterface $input, OutputInterface $output): void {
		$name = $this->askUserName($input, $output);
		$pass = $this->askPassword($input, $output);
		$role = $this->askRole($input, $output);
		$lang = $this->askLanguage($input, $output);
		$this->userManager->register($name, $pass, $role, $lang);
	}

	/**
	 * Ask for the username
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return string Username
	 */
	private function askUserName(InputInterface $input, OutputInterface $output): string {
		$username = $input->getOption('username');
		while (is_null($username)) {
			$helper = $this->getHelper('question');
			$question = new Question('Please enter the username: ');
			$name = $helper->ask($input, $output, $question);
			if ($this->userManager->uniqueUserName($name)) {
				$username = $name;
			}
		}
		return $username;
	}

	/**
	 * Ask for the user's password
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return string User's password
	 */
	private function askPassword(InputInterface $input, OutputInterface $output): string {
		$password = $input->getOption('password');
		while (is_null($password)) {
			$helper = $this->getHelper('question');
			$question = new Question('Please enter the new user\'s password: ');
			$password = $helper->ask($input, $output, $question);
		}
		return $password;
	}

	/**
	 * Ask for the user's role
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return string User's role
	 */
	private function askRole(InputInterface $input, OutputInterface $output): string {
		$role = $input->getOption('role');
		$roles = ['power', 'normal'];
		while (is_null($role) || !in_array($role, $roles, true)) {
			$helper = $this->getHelper('question');
			$question = new ChoiceQuestion('Please enter the user\'s role: ', $roles, 'normal');
			$role = $helper->ask($input, $output, $question);
		}
		return $role;
	}

	/**
	 * Ask for the user's language
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return string User's language
	 */
	private function askLanguage(InputInterface $input, OutputInterface $output): string {
		$language = $input->getOption('language');
		$languages = ['en'];
		while (is_null($language) || !in_array($language, $languages, true)) {
			$helper = $this->getHelper('question');
			$question = new ChoiceQuestion('Please enter the user\'s language: ', $languages, 'en');
			$language = $helper->ask($input, $output, $question);
		}
		return $language;
	}


}
