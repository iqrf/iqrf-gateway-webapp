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
use DateTime;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CLI command for adding a new API key
 */
class ApiKeyAddCommand extends EntityManagerCommand {

	/**
	 * @var string Command name
	 */
	protected static $defaultName = 'api-key:add';

	/**
	 * Configures the user add command
	 */
	protected function configure(): void {
		$this->setName(self::$defaultName);
		$this->setDescription('Adds a new API key');
		$definitions = [
			new InputOption('description', ['d'], InputOption::VALUE_OPTIONAL, 'API key description'),
			new InputOption('expiration', ['e'], InputOption::VALUE_OPTIONAL, 'API key expiration date'),
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
		$style->title('Add a new API key');
		$description = $this->askDescription($input, $output);
		$expiration = $this->askExpiration($input, $output);
		$apiKey = new ApiKey($description, $expiration);
		$this->entityManager->persist($apiKey);
		$this->entityManager->flush();
		$style->success('API key ' . $apiKey->getKey() . ' has been added!');
		return 0;
	}

	/**
	 * Asks for the API key description
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return string Username
	 */
	private function askDescription(InputInterface $input, OutputInterface $output): string {
		$description = $input->getOption('description');
		while ($description === null) {
			$helper = $this->getHelper('question');
			$question = new Question('Please enter the API key description: ');
			$description = $helper->ask($input, $output, $question);
		}
		return $description;
	}

	/**
	 * Asks for the API key expiration date
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return DateTime API key expiration date
	 */
	private function askExpiration(InputInterface $input, OutputInterface $output): ?DateTime {
		$expiration = $input->getOption('expiration');
		if ($expiration !== null) {
			return new DateTime($expiration);
		}
		$helper = $this->getHelper('question');
		$question = new Question('Please enter the API key expiration date: ');
		$expiration = $helper->ask($input, $output, $question);
		return $expiration === null ? null : new DateTime($expiration);
	}

}
