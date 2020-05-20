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
use App\Models\Database\Entities\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * User base command
 */
abstract class UserCommand extends Command {

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
	 * Asks for the username
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return User Information about the user
	 */
	protected function askUserName(InputInterface $input, OutputInterface $output): User {
		$username = $input->getOption('username');
		$user = null;
		if ($username !== null) {
			$user = $this->userManager->getUser($username);
		}
		while ($user === null) {
			$helper = $this->getHelper('question');
			$userNames = $this->userManager->listUserNames();
			$question = new ChoiceQuestion('Please enter the username: ', $userNames);
			$username = $helper->ask($input, $output, $question);
			$user = $this->userManager->getUser($username);
		}
		return $user;
	}

}
