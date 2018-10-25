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

use App\ConsoleModule\Models\ConsoleUserManager;
use Nette\SmartObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * CLI command for user management
 */
class UserRemoveCommand extends Command {

	use SmartObject;

	/**
	 * @var string Command name
	 */
	protected static $defaultName = 'user:remove';

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
		$this->setName('user:remove');
		$this->setDescription('Removes webapp\'s user');
		$definitions = [
			new InputOption('username', ['u', 'user'], InputOption::VALUE_OPTIONAL, 'Username of the removed user'),
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
		$this->confirmAction($input, $output);
		$this->userManager->delete($user['id']);
	}

	/**
	 * Ask for the username
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return array Information about the user
	 */
	private function askUserName(InputInterface $input, OutputInterface $output): array {
		$username = $input->getOption('username');
		$user = $this->userManager->getUser($username);
		while (is_null($user)) {
			$helper = $this->getHelper('question');
			$userNames = $this->userManager->listUserNames();
			$question = new ChoiceQuestion('Please enter the username: ', $userNames);
			$username = $helper->ask($input, $output, $question);
			$user = $this->userManager->getUser($username);
		}
		return $user;
	}

	/**
	 * Confirm the action
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 */
	private function confirmAction(InputInterface $input, OutputInterface $output): void {
		$helper = $this->getHelper('question');
		$question = new ConfirmationQuestion('Do you really want to remove this user? ', false);
		if (!$helper->ask($input, $output, $question)) {
			return;
		}
	}


}
