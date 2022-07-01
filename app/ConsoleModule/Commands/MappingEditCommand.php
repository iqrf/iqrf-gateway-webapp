<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CLI command to edit existing mapping
 */
class MappingEditCommand extends MappingCommand {

	/**
	 * @var string|null Command name
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected static $defaultName = 'mapping:edit';

	/**
	 * Configures the mapping edit command
	 */
	protected function configure(): void {
		$this->setDescription('Adds a new mapping');
		$definitions = [
			new InputOption('mapping-id', ['i'], InputOption::VALUE_REQUIRED, 'Mapping ID'),
			new InputOption('type', ['t'], InputOption::VALUE_REQUIRED, 'Mapping type'),
			new InputOption('name', ['N'], InputOption::VALUE_REQUIRED, 'Mapping name'),
			new InputOption('interface', ['I'], InputOption::VALUE_REQUIRED, 'Mapping device name'),
			new InputOption('bus-pin', ['b'], InputOption::VALUE_REQUIRED, 'Mapping bus enable pin number'),
			new InputOption('pgm-pin', ['p'], InputOption::VALUE_REQUIRED, 'Mapping programming mode switch pin number'),
			new InputOption('power-pin', ['P'], InputOption::VALUE_REQUIRED, 'Mapping power enable pin number'),
			new InputOption('baud-rate', ['r'], InputOption::VALUE_OPTIONAL, 'Mapping UART baud rate'),
		];
		$this->setDefinition(new InputDefinition($definitions));
	}

	/**
	 * Executes the mapping edit command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$style = new SymfonyStyle($input, $output);
		$style->title('Edit mapping');
		$mapping = $this->askId($input, $output);
		$style->warning('Mapping selected to edit: ' . $mapping->getName() . ' (' . $mapping->getType() . ')');
		$oldName = $mapping->getName();
		$name = $this->askName($input, $output);
		$type = $this->askExistingType($mapping, $input, $output);
		$interface = $this->askInterface($input, $output);
		$busPin = $this->askBusPin($input, $output);
		$pgmPin = $this->askPgmPin($input, $output);
		$powerPin = $this->askPowerPin($input, $output);
		$mapping->setName($name);
		$mapping->setType($type);
		$mapping->setInterface($interface);
		$mapping->setBusPin($busPin);
		$mapping->setPgmPin($pgmPin);
		$mapping->setPowerPin($powerPin);
		if ($type === Mapping::TYPE_UART) {
			$baudRate = $this->askExistingBaudRate($mapping, $input, $output);
			$mapping->setBaudRate($baudRate);
		}
		$this->entityManager->persist($mapping);
		$this->entityManager->flush();
		$style->success('Mapping "' . $oldName . '" has been edited.');
		return 0;
	}

	/**
	 * Asks for the existing mapping type
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return string Mapping type
	 */
	private function askExistingType(Mapping $mapping, InputInterface $input, OutputInterface $output): string {
		$type = $input->getOption('type');
		while ($type === null || !in_array($type, Mapping::TYPES, true)) {
			$helper = $this->getHelper('question');
			$question = new ChoiceQuestion('Please select mapping type: ', Mapping::TYPES, $mapping->getType());
			$type = $helper->ask($input, $output, $question);
		}
		return $type;
	}

	/**
	 * Asks for the existing mapping UART baud rate
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Mapping UART baud rate
	 */
	protected function askExistingBaudRate(Mapping $mapping, InputInterface $input, OutputInterface $output): int {
		$baudRate = $input->getOption('baud-rate');
		while ($baudRate === null || !ctype_digit($baudRate)) {
			$helper = $this->getHelper('question');
			$question = new ChoiceQuestion('Please select the mapping UART baud rate: ', Mapping::BAUD_RATES, $mapping->getBaudRate());
			$baudRate = $helper->ask($input, $output, $question);
		}
		return (int) $baudRate;
	}

}
