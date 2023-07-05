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

use App\Models\Database\Enums\MappingBaudRate;
use App\Models\Database\Enums\MappingType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use ValueError;

/**
 * CLI command to edit existing mapping
 */
#[AsCommand(name: 'mapping:edit', description: 'Edits an existing mapping profile')]
class MappingEditCommand extends MappingCommand {

	/**
	 * Configures the mapping edit command
	 */
	protected function configure(): void {
		$definitions = [
			new InputOption('mapping-id', ['i'], InputOption::VALUE_REQUIRED, 'Mapping ID'),
			new InputOption('type', ['t'], InputOption::VALUE_REQUIRED, 'Mapping type'),
			new InputOption('name', ['N'], InputOption::VALUE_REQUIRED, 'Mapping name'),
			new InputOption('device-type', ['d'], InputOption::VALUE_REQUIRED, 'Device type'),
			new InputOption('interface', ['I'], InputOption::VALUE_REQUIRED, 'Device name'),
			new InputOption('bus-pin', ['b'], InputOption::VALUE_REQUIRED, 'Bus enable pin number'),
			new InputOption('pgm-pin', ['p'], InputOption::VALUE_REQUIRED, 'Programming mode switch pin number'),
			new InputOption('power-pin', ['P'], InputOption::VALUE_REQUIRED, 'Power enable pin number'),
			new InputOption('baud-rate', ['r'], InputOption::VALUE_OPTIONAL, 'UART baud rate'),
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
		$style->title('Edit mapping profile');
		$mapping = $this->askId($input, $output);
		$style->warning('Mapping profile selected to edit: ' . $mapping->getName() . ' (' . $mapping->getType()->name . ')');
		$oldName = $mapping->getName();
		$name = $this->askName($input, $output);
		try {
			$type = $this->askType($input, $output, $mapping->getType());
		} catch (ValueError) {
			$style->error('Invalid mapping type: ' . $input->getOption('type'));
			return 1;
		}
		try {
			$deviceType = $this->askDeviceType($input, $output, $mapping->getDeviceType());
		} catch (ValueError) {
			$style->error('Invalid device type: ' . $input->getOption('device-type'));
			return 1;
		}
		$interface = $this->askInterface($input, $output);
		$busPin = $this->askBusPin($input, $output);
		$pgmPin = $this->askPgmPin($input, $output);
		$powerPin = $this->askPowerPin($input, $output);
		$mapping->setName($name);
		$mapping->setType($type);
		$mapping->setDeviceType($deviceType);
		$mapping->setInterface($interface);
		$mapping->setBusPin($busPin);
		$mapping->setPgmPin($pgmPin);
		$mapping->setPowerPin($powerPin);
		if ($type === MappingType::UART) {
			$baudRate = $this->askBaudRate($input, $output, $mapping->getBaudRate() ?? MappingBaudRate::Default);
			$mapping->setBaudRate($baudRate);
		}
		$this->entityManager->persist($mapping);
		$this->entityManager->flush();
		$style->success('Mapping profile ' . $oldName . ' has been edited.');
		return 0;
	}

}
