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

use App\CoreModule\Model\UserManager;
use Nette\SmartObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * CLI command for user management
 */
class UserPasswordCommand extends Command {

	use SmartObject;

	/**
	 * @var string Command name
	 */
	protected static $defaultName = 'user:password';

	/**
	 * @var UserManager User manager
	 */
	protected $userManager;

	/**
	 * Constructor
	 * @param UserManager $userManager User manager
	 */
	public function __construct(UserManager $userManager) {
		parent::__construct();
		$this->userManager = $userManager;
	}

	/**
	 * Configure the command
	 */
	protected function configure(): void {
		$this->setName('user:password');
		$this->setDescription('Change webapp\'s user\'s password');
		$definitions = [
			new InputOption('username', ['u', 'user'], InputOption::VALUE_OPTIONAL, 'Username of the edited user'),
			new InputOption('password', ['p', 'pass'], InputOption::VALUE_OPTIONAL, 'New user\'s password'),
		];
		$this->setDefinition(new InputDefinition($definitions));
	}

	/**
	 * Execute the command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 */
	protected function execute(InputInterface $input, OutputInterface $output): void {
		$user = $this->askUserName($input, $output);
		$pass = $this->askPassword($input, $output);
		$this->userManager->editPassword($user['id'], $pass);
	}

	/**
	 * Ask for the username
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return array Information about the user
	 */
	private function askUserName(InputInterface $input, OutputInterface $output): array {
		$username = $input->getOption('username');
		$user = $this->getUser($username);
		while (is_null($user)) {
			$helper = $this->getHelper('question');
			$question = new Question('Please enter the username: ');
			$username = $helper->ask($input, $output, $question);
			$user = $this->getUser($username);
		}
		return $user;
	}

	/**
	 * Get information about the user from username
	 * @param null|string $username Username
	 * @return array|null Information about the user
	 */
	private function getUser(?string $username): ?array {
		foreach ($this->userManager->getUsers() as $user) {
			if ($user['username'] === $username) {
				return $user;
			}
		}
		return null;
	}

	/**
	 * Ask for the user's password
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return string New user's password
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


}
