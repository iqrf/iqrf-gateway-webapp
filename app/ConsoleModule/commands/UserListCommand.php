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
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI command for user management
 */
class UserListCommand extends Command {

	use SmartObject;

	/**
	 * @var string Command name
	 */
	protected static $defaultName = 'user:list';

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
		$this->setName('user:list');
		$this->setDescription('Lists webapp\'s users');
	}

	/**
	 * Execute the command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 */
	protected function execute(InputInterface $input, OutputInterface $output): void {
		$header = ['ID', 'Username', 'Role', 'Language'];
		$table = new Table($output);
		$table->setHeaders($header);
		$table->setRows($this->userManager->listUsers());
		$table->render();
	}

}
