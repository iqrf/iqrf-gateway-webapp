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

use App\Models\Database\Entities\ApiKey;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CLI command for deleting API keys
 */
class ApiKeyDeleteCommand extends ApiKeyCommand {

	/**
	 * @var string Command name
	 */
	protected static $defaultName = 'api-key:delete';

	/**
	 * Configures the user add command
	 */
	protected function configure(): void {
		$this->setDescription('Deletes an API key');
		$definitions = [
			new InputOption('id', ['i'], InputOption::VALUE_OPTIONAL, 'API key ID to delete'),
		];
		$this->setDefinition(new InputDefinition($definitions));
	}

	/**
	 * Executes the API key delete command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$style = new SymfonyStyle($input, $output);
		$style->title('Delete the API key');
		$apiKey = $this->deleteAskId($input, $output);
		if ($apiKey === null) {
			$style->error('API key with specified ID does not exist.');
			return Command::FAILURE;
		}
		$helper = $this->getHelper('question');
		$question = new ConfirmationQuestion('Do you really want to delete API key "' . $apiKey->getDescription() . '"? ', false);
		if (!$helper->ask($input, $output, $question)) {
			return Command::SUCCESS;
		}
		$this->entityManager->remove($apiKey);
		$this->entityManager->flush();
		$style->success('API key "' . $apiKey->getDescription() . '" has been deleted.');
		return Command::SUCCESS;
	}

	/**
	 * Asks for API key ID
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return ApiKey|null Api key
	 */
	protected function deleteAskId(InputInterface $input, OutputInterface $output): ?ApiKey {
		$apiKeyId = $input->getOption('id');
		$apiKey = ($apiKeyId !== null) ? $this->repository->find($apiKeyId) : null;
		if (!$input->isInteractive()) {
			return $apiKey;
		}
		$style = new SymfonyStyle($input, $output);
		$helper = $this->getHelper('question');
		while ($apiKey === null) {
			$style->error('API key with ID "' . $apiKeyId . '" does not exist.');
			$apiKeys = $this->repository->listWithDescription();
			$question = new ChoiceQuestion('Please enter API key ID: ', $apiKeys);
			$apiKeyId = array_search($helper->ask($input, $output, $question), $apiKeys, true);
			$apiKey = $this->repository->find($apiKeyId);
		}
		assert($apiKey instanceof ApiKey);
		return $apiKey;
	}

}
