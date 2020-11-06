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

use App\Models\Database\Entities\Mapping;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CLI command to add a new mapping
 */
class MappingAddCommand extends MappingCommand {

	/**
	 * @var string Command name
	 */
	protected static $defaultName = 'mapping:add';

	/**
	 * Configures the mapping add command
	 */
	protected function configure(): void {
		$this->setDescription('Adds a new mapping');
		$definitions = [
			new InputOption('type', ['type'], InputOption::VALUE_REQUIRED, 'Mapping type'),
			new InputOption('name', ['name'], InputOption::VALUE_REQUIRED, 'Mapping name'),
			new InputOption('interface', ['interface'], InputOption::VALUE_REQUIRED, 'Mapping device name'),
			new InputOption('bus pin', ['bus-pin'], InputOption::VALUE_REQUIRED, 'Mapping bus enable pin number'),
			new InputOption('pgm pin', ['pgm-pin'], InputOption::VALUE_REQUIRED, 'Mapping programming mode switch pin number'),
			new InputOption('power pin', ['power-pin'], InputOption::VALUE_REQUIRED, 'Mapping power enable pin number'),
			new InputOption('baud rate', ['baud-rate'], InputOption::VALUE_OPTIONAL, 'Mapping UART baud rate'),
		];
		$this->setDefinition(new InputDefinition($definitions));
	}

	/**
	 * Executes the mapping add command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$style = new SymfonyStyle($input, $output);
		$style->title('Add a new mapping');
		$type = $this->askType($input, $output);
		$name = $this->askName($input, $output);
		$interface = $this->askInterface($input, $output);
		$busPin = $this->askBusPin($input, $output);
		$pgmPin = $this->askPgmPin($input, $output);
		$powerPin = $this->askPowerPin($input, $output);
		if ($type === 'uart') {
			$baudRate = $this->askBaudRate($input, $output);
			$mapping = new Mapping($type, $name, $interface, $busPin, $pgmPin, $powerPin, $baudRate);
		} else {
			$mapping = new Mapping($type, $name, $interface, $busPin, $pgmPin, $powerPin);
		}
		$this->entityManager->persist($mapping);
		$this->entityManager->flush();
		$style->success('Mapping ' . $mapping->getName() . ' has been added!');
		return 0;
	}

}
