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

use App\ConsoleModule\Models\ConsoleUserManager;
use Nette\SmartObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * CLI command for user management
 */
class UserEditCommand extends Command {

	use SmartObject;

	/**
	 * @var string Command name
	 */
	protected static $defaultName = 'user:edit';

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
	 * Configures the user edit command
	 */
	protected function configure(): void {
		$this->setName('user:edit');
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
		$this->userManager->edit($user['id'], $user['username'], $role, $language);
		return 0;
	}

	/**
	 * Asks for the username
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return mixed[] Information about the user
	 */
	private function askUserName(InputInterface $input, OutputInterface $output): array {
		$username = $input->getOption('username');
		$user = $this->userManager->getUser($username);
		while ($user === null) {
			$helper = $this->getHelper('question');
			$userNames = $this->userManager->listUserNames();
			$question = new ChoiceQuestion('Please enter the username: ', $userNames);
			$username = $helper->ask($input, $output, $question);
			$user = $this->userManager->getUser($username);
		}
		return $user;
	}

	/**
	 * Asks for the user's role
	 * @param mixed[] $user Information about the user
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return string New user's role
	 */
	private function askRole(array $user, InputInterface $input, OutputInterface $output): string {
		$role = $input->getOption('role');
		$roles = ['power', 'normal'];
		while ($role === null || !in_array($role, $roles, true)) {
			$helper = $this->getHelper('question');
			$question = new ChoiceQuestion('Please enter the user\'s role: ', $roles, $user['role']);
			$role = $helper->ask($input, $output, $question);
		}
		return $role;
	}

	/**
	 * Asks for the user's language
	 * @param mixed[] $user Information about the user
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return string New user's language
	 */
	private function askLanguage(array $user, InputInterface $input, OutputInterface $output): string {
		$language = $input->getOption('language');
		$languages = ['en'];
		while ($language === null || !in_array($language, $languages, true)) {
			$helper = $this->getHelper('question');
			$question = new ChoiceQuestion('Please enter the user\'s language: ', $languages, $user['language']);
			$language = $helper->ask($input, $output, $question);
		}
		return $language;
	}

}
