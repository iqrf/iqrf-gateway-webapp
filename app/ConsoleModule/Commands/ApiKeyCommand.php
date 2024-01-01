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

use App\Models\Database\Entities\ApiKey;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\ApiKeyRepository;
use DateTime;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

abstract class ApiKeyCommand extends EntityManagerCommand {

	/**
	 * @var ApiKeyRepository API key database repository
	 */
	protected readonly ApiKeyRepository $repository;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 */
	public function __construct(EntityManager $entityManager) {
		parent::__construct($entityManager);
		$this->repository = $entityManager->getApiKeyRepository();
	}

	/**
	 * Asks for the API key description
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return string Username
	 */
	protected function askDescription(InputInterface $input, OutputInterface $output): string {
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
	protected function askExpiration(InputInterface $input, OutputInterface $output): ?DateTime {
		$expiration = $input->getOption('expiration');
		if ($expiration !== null) {
			if ($expiration === '') {
				throw new Exception();
			}
			return new DateTime($expiration);
		}
		$helper = $this->getHelper('question');
		$question = new Question('Please enter the API key expiration date: ');
		$expiration = $helper->ask($input, $output, $question);
		return $expiration === null ? null : new DateTime($expiration);
	}

	/**
	 * Asks for the API key ID
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return ApiKey API key
	 */
	protected function askId(InputInterface $input, OutputInterface $output): ApiKey {
		$apiKeyId = $input->getOption('id');
		$apiKey = ($apiKeyId !== null) ? $this->repository->find($apiKeyId) : null;
		$helper = $this->getHelper('question');
		while ($apiKey === null) {
			$apiKeys = $this->repository->listWithDescription();
			$question = new ChoiceQuestion('Please enter API key ID: ', $apiKeys);
			$apiKeyId = array_search($helper->ask($input, $output, $question), $apiKeys, true);
			$apiKey = $this->repository->find($apiKeyId);
		}
		assert($apiKey instanceof ApiKey);
		return $apiKey;
	}

}
