<?php

/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
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
use App\Models\Database\Enums\MappingType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use ValueError;

/**
 * CLI command to add a new mapping
 */
#[AsCommand(name: 'mapping:add', description: 'Adds a new mapping profile')]
class MappingAddCommand extends MappingCommand {

	/**
	 * Configures the mapping add command
	 */
	protected function configure(): void {
		$definitions = [
			new InputOption('type', ['t'], InputOption::VALUE_REQUIRED, 'Mapping type'),
			new InputOption('name', ['N'], InputOption::VALUE_REQUIRED, 'Mapping name'),
			new InputOption('device-type', ['d'], InputOption::VALUE_REQUIRED, 'Device type'),
			new InputOption('interface', ['I'], InputOption::VALUE_REQUIRED, 'Device interface'),
			new InputOption('bus-pin', ['b'], InputOption::VALUE_REQUIRED, 'Bus enable pin number'),
			new InputOption('pgm-pin', ['p'], InputOption::VALUE_REQUIRED, 'Programming mode switch pin number'),
			new InputOption('power-pin', ['P'], InputOption::VALUE_REQUIRED, 'Power enable pin number'),
			new InputOption('baud-rate', ['r'], InputOption::VALUE_OPTIONAL, 'UART baud rate'),
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
		$style->title('Add a new mapping profile');
		try {
			$type = $this->askType($input, $output);
		} catch (ValueError) {
			$style->error('Invalid mapping type: ' . $input->getOption('type'));
			return 1;
		}
		$name = $this->askName($input, $output);
		try {
			$deviceType = $this->askDeviceType($input, $output);
		} catch (ValueError) {
			$style->error('Invalid device type: ' . $input->getOption('device-type'));
			return 1;
		}
		$interface = $this->askInterface($input, $output);
		$busPin = $this->askBusPin($input, $output);
		$pgmPin = $this->askPgmPin($input, $output);
		$powerPin = $this->askPowerPin($input, $output);
		$mapping = new Mapping($type, $name, $deviceType, $interface, $busPin, $pgmPin, $powerPin);
		if ($type === MappingType::UART) {
			try {
				$baudRate = $this->askBaudRate($input, $output);
				$mapping->setBaudRate($baudRate);
			} catch (ValueError) {
				$style->error('Invalid UART baud rate: ' . $input->getOption('baud-rate'));
				return 1;
			}
		}
		$this->entityManager->persist($mapping);
		$this->entityManager->flush();
		$style->success('Mapping profile ' . $mapping->getName() . ' has been added!');
		return 0;
	}

}
