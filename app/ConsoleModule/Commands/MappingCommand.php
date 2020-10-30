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

use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\MappingRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

abstract class MappingCommand extends EntityManagerCommand {

	/**
	 * @var MappingRepository Mapping database repository
	 */
	protected $repository;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 * @param string|null $name Command name
	 */
	public function __construct(EntityManager $entityManager, ?string $name = null) {
		parent::__construct($entityManager, $name);
		$this->repository = $entityManager->getMappingRepository();
	}

	/**
	 * Asks for the mapping name
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return string Mapping name
	 */
	protected function askName(InputInterface $input, OutputInterface $output): string {
		$name = $input->getOption('name');
		while ($name === null) {
			$helper = $this->getHelper('question');
			$question = new Question('Please enter the mapping name: ');
			$name = $helper->ask($input, $output, $question);
		}
		return $name;
	}

	/**
	 * Asks for the mapping device name
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return string Mapping device name
	 */
	protected function askInterface(InputInterface $input, OutputInterface $output): string {
		$interface = $input->getOption('interface');
		while ($interface === null) {
			$helper = $this->getHelper('question');
			$question = new Question('Please enter the mapping device name: ');
			$interface = $helper->ask($input, $output, $question);
		}
		return $interface;
	}

	/**
	 * Asks for the mapping bus enable pin
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Mapping bus enable pin
	 */
	protected function askBusPin(InputInterface $input, OutputInterface $output): int {
		$busPin = $input->getOption('bus pin');
		while ($busPin === null || !ctype_digit($busPin)) {
			$helper = $this->getHelper('question');
			$question = new Question('Please enter the mapping bus enable pin number :');
			$busPin = $helper->ask($input, $output, $question);

		}
		return intval($busPin);
	}

	/**
	 * Asks for the mapping programming mode switch pin
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Mapping programming mode switch pin
	 */
	protected function askPgmPin(InputInterface $input, OutputInterface $output): int {
		$pgmPin = $input->getOption('pgm pin');
		while ($pgmPin === null || !ctype_digit($pgmPin)) {
			$helper = $this->getHelper('question');
			$question = new Question('Please enter the mapping programming mode switch pin number :');
			$pgmPin = $helper->ask($input, $output, $question);

		}
		return intval($pgmPin);
	}

	/**
	 * Asks for the mapping power enable pin
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Mapping power enable pin
	 */
	protected function askPowerPin(InputInterface $input, OutputInterface $output): int {
		$powerPin = $input->getOption('power pin');
		while ($powerPin === null || !ctype_digit($powerPin)) {
			$helper = $this->getHelper('question');
			$question = new Question('Please enter the mapping power enable pin number :');
			$powerPin = $helper->ask($input, $output, $question);

		}
		return intval($powerPin);
	}

}
