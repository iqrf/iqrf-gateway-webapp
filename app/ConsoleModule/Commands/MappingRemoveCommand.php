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

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CLI command to remove exiting mapping
 */
class MappingRemoveCommand extends MappingCommand {

	/**
	 * @var string|null Command name
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected static $defaultName = 'mapping:remove';

	/**
	 * Configures the mapping remove command
	 */
	protected function configure(): void {
		$this->setDescription('Remove mapping');
		$definitions = [
			new InputOption('mapping-id', ['i'], InputOption::VALUE_REQUIRED, 'Mapping ID'),
		];
		$this->setDefinition(new InputDefinition($definitions));
	}

	/**
	 * Executes the mapping remove command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$style = new SymfonyStyle($input, $output);
		$style->title('Remove a mapping');
		$mapping = $this->askId($input, $output);
		$helper = $this->getHelper('question');
		$question = new ConfirmationQuestion('Do you really want to remove mapping "' . $mapping->getName() . ' (' . $mapping->getType() . ')"? (Y/N) ', false);
		if (!$helper->ask($input, $output, $question)) {
			return 0;
		}
		$this->entityManager->remove($mapping);
		$this->entityManager->flush();
		$style->success('Mapping "' . $mapping->getName() . '" has been removed.');
		return 0;
	}

}
