<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

use App\CoreModule\Exceptions\FeatureNotFoundException;
use Nette\IOException;
use Nette\Neon\Exception as NeonException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CLI command for disabling features
 */
#[AsCommand(name: 'feature:disable', description: 'Disables webapp\'s features')]
class FeatureDisableCommand extends FeatureCommand {

	/**
	 * Configures the feature disable command
	 */
	protected function configure(): void {
		$this->addArgument('names', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Names of disabled features');
	}

	/**
	 * Executes the feature disable command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$style = new SymfonyStyle($input, $output);
		$style->title('Disable features');
		$names = $input->getArgument('names');
		if (count($names) === 0) {
			$style->error('No features specified.');
			return 1;
		}
		try {
			$this->manager->setEnabled($names, false);
		} catch (IOException | NeonException $e) {
			$style->error('An error occurred while disabling features.');
			return 1;
		} catch (FeatureNotFoundException $e) {
			$style->error('Unknown feature ' . $e->getMessage() . '.');
			return 1;
		}
		if (count($names) === 1) {
			$message = sprintf('Feature %s has been successfully disabled.', $names[0]);
		} else {
			$message = sprintf('Features %s have been successfully disabled.', implode(', ', $names));
		}
		$style->success($message);
		$this->getApplication()->find('nette:cache:purge')->execute($input, $output);
		return 0;
	}

}
