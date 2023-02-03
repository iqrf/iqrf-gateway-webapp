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

use App\Models\Database\Entities\Mapping;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\MappingRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

abstract class MappingCommand extends EntityManagerCommand {

	/**
	 * @var MappingRepository Mapping database repository
	 */
	protected MappingRepository $repository;

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
	 * Asks for the mapping type
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @param string $current Default or current mapping type
	 * @return string Mapping type
	 */
	protected function askType(InputInterface $input, OutputInterface $output, string $current = Mapping::TYPE_SPI): string {
		$type = $input->getOption('type');
		while ($type === null || !in_array($type, Mapping::TYPES, true)) {
			$helper = $this->getHelper('question');
			$question = new ChoiceQuestion('Please select mapping type: ', Mapping::TYPES, $current);
			$type = $helper->ask($input, $output, $question);
		}
		return $type;
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
	 * Asks for device type
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @param string $current Default or current device type
	 * @return string Device type
	 */
	protected function askDeviceType(InputInterface $input, OutputInterface $output, string $current = Mapping::DEVICE_BOARD): string {
		$type = $input->getOption('device-type');
		while ($type === null || !in_array($type, Mapping::DEVICE_TYPES, true)) {
			$helper = $this->getHelper('question');
			$question = new ChoiceQuestion('Please selecte device type: ', Mapping::DEVICE_TYPES, $current);
			$type = $helper->ask($input, $output, $question);
		}
		return $type;
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
			$question = new Question('Please enter the mapping device interface: ');
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
		$busPin = $input->getOption('bus-pin');
		while ($busPin === null || !$this->isValidPinNumber($busPin)) {
			$helper = $this->getHelper('question');
			$question = new Question('Please enter the mapping bus enable pin number: ');
			$busPin = $helper->ask($input, $output, $question);

		}
		return (int) $busPin;
	}

	/**
	 * Asks for the mapping programming mode switch pin
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Mapping programming mode switch pin
	 */
	protected function askPgmPin(InputInterface $input, OutputInterface $output): int {
		$pgmPin = $input->getOption('pgm-pin');
		while ($pgmPin === null || !$this->isValidPinNumber($pgmPin)) {
			$helper = $this->getHelper('question');
			$question = new Question('Please enter the mapping programming mode switch pin number: ');
			$pgmPin = $helper->ask($input, $output, $question);

		}
		return (int) $pgmPin;
	}

	/**
	 * Asks for the mapping power enable pin
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Mapping power enable pin
	 */
	protected function askPowerPin(InputInterface $input, OutputInterface $output): int {
		$powerPin = $input->getOption('power-pin');
		while ($powerPin === null || !$this->isValidPinNumber($powerPin)) {
			$helper = $this->getHelper('question');
			$question = new Question('Please enter the mapping power enable pin number: ');
			$powerPin = $helper->ask($input, $output, $question);

		}
		return (int) $powerPin;
	}

	/**
	 * Asks for the mapping UART baud rate
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Mapping UART baud rate
	 */
	protected function askBaudRate(InputInterface $input, OutputInterface $output, int $current = Mapping::BAUD_RATE_DEFAULT): int {
		$baudRate = $input->getOption('baud-rate');
		while ($baudRate === null || !ctype_digit($baudRate)) {
			$helper = $this->getHelper('question');
			$question = new ChoiceQuestion('Please select the mapping UART baud rate: ', Mapping::BAUD_RATES, $current);
			$baudRate = $helper->ask($input, $output, $question);
		}
		return (int) $baudRate;
	}

	/**
	 * Asks for the mapping id
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return Mapping Mapping
	 */
	protected function askId(InputInterface $input, OutputInterface $output): Mapping {
		$mappingId = $input->getOption('mapping-id');
		$mapping = ($mappingId !== null) ? $this->repository->find($mappingId) : null;
		$helper = $this->getHelper('question');
		while ($mapping === null) {
			$mappings = $this->repository->listMappingNamesWithTypes();
			$question = new ChoiceQuestion('Please select mapping ID: ', $mappings);
			$mappingId = array_search($helper->ask($input, $output, $question), $mappings, true);
			$mapping = $this->repository->find($mappingId);
		}
		assert($mapping instanceof Mapping);
		return $mapping;
	}

	/**
	 * Checks if number is a valid pin number
	 * @param string $number Pin number candidate
	 * @return bool true if candidate is valid number, false otherwise
	 */
	private function isValidPinNumber(string $number): bool {
		if ($number[0] === '-') {
			return ctype_digit(substr($number, 1));
		}
		return ctype_digit($number);
	}

}
