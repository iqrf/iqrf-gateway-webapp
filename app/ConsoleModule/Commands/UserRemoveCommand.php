<?php

/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
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

use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * CLI command for user management
 */
#[AsCommand(name: 'user:remove', description: 'Removes webapp\'s user')]
class UserRemoveCommand extends UserCommand {

	/**
	 * Configures the user remove command
	 */
	protected function configure(): void {
		$definitions = [
			new InputOption('username', ['u', 'user'], InputOption::VALUE_OPTIONAL, 'Username of the removed user'),
		];
		$this->setDefinition(new InputDefinition($definitions));
	}

	/**
	 * Executes the user remove command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$user = $this->findUserByName($input, $output);
		if ($input->isInteractive() && !$this->confirmAction($input, $output)) {
			return 0;
		}
		$this->entityManager->remove($user);
		$this->entityManager->flush();
		return 0;
	}

	/**
	 * Confirms the action
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return bool Confirmation
	 * @throws RuntimeException When question helper is not found
	 */
	private function confirmAction(InputInterface $input, OutputInterface $output): bool {
		$helper = $this->getQuestionHelper();
		$question = new ConfirmationQuestion('Do you really want to remove this user? ', false);
		return $helper->ask($input, $output, $question);
	}

}
