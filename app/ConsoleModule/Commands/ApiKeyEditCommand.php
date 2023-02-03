<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

use App\Exceptions\ApiKeyExpirationPassedException;
use App\Exceptions\ApiKeyInvalidExpirationException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CLI command for editing API keys
 */
class ApiKeyEditCommand extends ApiKeyCommand {

	/**
	 * @var string|null Command name
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected static $defaultName = 'api-key:edit';

	/**
	 * Configures the user add command
	 */
	protected function configure(): void {
		$this->setDescription('Edits an API key');
		$definitions = [
			new InputOption('id', ['i'], InputOption::VALUE_OPTIONAL, 'API key ID to edit'),
			new InputOption('description', ['d'], InputOption::VALUE_OPTIONAL, 'New API key description'),
			new InputOption('expiration', ['e'], InputOption::VALUE_OPTIONAL, 'New API key expiration'),
		];
		$this->setDefinition(new InputDefinition($definitions));
	}

	/**
	 * Executes the API key edit command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$style = new SymfonyStyle($input, $output);
		$apiKey = $this->askId($input, $output);
		$description = $this->askDescription($input, $output);
		$apiKey->setDescription($description);
		try {
			$expiration = $this->askExpiration($input, $output);
			$apiKey->setExpiration($expiration);
		} catch (ApiKeyInvalidExpirationException $e) {
			$style->error('Invalid time and date format.');
			return Command::FAILURE;
		} catch (ApiKeyExpirationPassedException $e) {
			$style->error('Expiration date has already passed.');
			return Command::FAILURE;
		}
		$this->entityManager->persist($apiKey);
		$this->entityManager->flush();
		$style->success('API key "' . $apiKey->getDescription() . '" has been edited.');
		return Command::SUCCESS;
	}

}
