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

use App\Exceptions\ApiKeyInvalidExpirationException;
use App\Models\Database\Entities\ApiKey;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CLI command for adding a new API key
 */
#[AsCommand(name: 'api-key:add', description: 'Adds a new API key')]
class ApiKeyAddCommand extends ApiKeyCommand {

	/**
	 * Configures the user add command
	 */
	protected function configure(): void {
		$definitions = [
			new InputOption('description', ['d'], InputOption::VALUE_OPTIONAL, 'API key description'),
			new InputOption('expiration', ['e'], InputOption::VALUE_OPTIONAL, 'API key expiration date'),
			new InputOption('no-formatting', null, InputOption::VALUE_NONE, 'Output API key without formatting.'),
		];
		$this->setDefinition(new InputDefinition($definitions));
	}

	/**
	 * Executes the API key add command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$style = new SymfonyStyle($input, $output);
		$format = ($input->getParameterOption('--no-formatting') === false);
		if ($format) {
			$style->title('Add a new API key');
		}
		$description = $this->askDescription($input, $output);
		try {
			$expiration = $this->askExpiration($input, $output);
		} catch (ApiKeyInvalidExpirationException) {
			$style->error('Invalid time and date format.');
			return Command::FAILURE;
		}
		$apiKey = new ApiKey($description, $expiration);
		if ($apiKey->isExpired()) {
			$style->error('Expiration date has already passed.');
			return Command::FAILURE;
		}
		$this->entityManager->persist($apiKey);
		$this->entityManager->flush();
		if ($format) {
			$style->success('API key ' . $apiKey->getKey() . ' has been added!');
		} else {
			$style->text($apiKey->getKey());
		}
		return 0;
	}

}
