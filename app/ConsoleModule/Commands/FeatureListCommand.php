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

use Nette\Neon\Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CLI command for listing features
 */
#[AsCommand(name: 'feature:list', description: 'Lists webapp\'s features')]
class FeatureListCommand extends FeatureCommand {

	/**
	 * Executes the features list command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$header = ['Full name', 'Name', 'Status'];
		$style = new SymfonyStyle($input, $output);
		$style->title('Feature list');
		try {
			$features = $this->manager->list();
			$style->table($header, $features);
		} catch (Exception) {
			$style->error('An error occurred while reading configuration file.');
			return 1;
		}
		return 0;
	}

}
