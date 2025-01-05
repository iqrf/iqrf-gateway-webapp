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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CLI command for listing API keys
 */
class ApiKeyListCommand extends ApiKeyCommand {

	/**
	 * @var string|null Command name
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected static $defaultName = 'api-key:list';

	/**
	 * Configures the API key list command
	 */
	protected function configure(): void {
		$this->setDescription('Lists API keys');
	}

	/**
	 * Executes the API key list command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$apiKeys = [];
		foreach ($this->repository->findAll() as $apiKey) {
			$expiration = $apiKey->getExpiration() === null ? 'none' : $apiKey->getExpiration()->format('c');
			$apiKeys[] = [$apiKey->getId(), $apiKey->getDescription(), $expiration];
		}
		$style = new SymfonyStyle($input, $output);
		$style->title('List API keys');
		$style->table(['Key ID', 'Description', 'Expiration date'], $apiKeys);
		return 0;
	}

}
