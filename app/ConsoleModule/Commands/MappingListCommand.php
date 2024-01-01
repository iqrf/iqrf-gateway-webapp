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

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CLI command to list existing mappings
 */
#[AsCommand(name: 'mapping:list', description: 'Lists mappings')]
class MappingListCommand extends MappingCommand {

	/**
	 * Executes the mapping list command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$mappings = [];
		foreach ($this->repository->findAll() as $mapping) {
			$mappings[] = [
				$mapping->getId(),
				$mapping->getType()->name,
				$mapping->getName(),
				$mapping->getDeviceType()->name,
				$mapping->getInterface(),
				$mapping->getBusPin(),
				$mapping->getPgmPin(),
				$mapping->getPowerPin(),
				$mapping->getBaudRate()->value,
				$mapping->getI2cPin(),
				$mapping->getSpiPin(),
				$mapping->getUartPin(),
			];
		}
		$style = new SymfonyStyle($input, $output);
		$style->title('List mappings (* IQRF Gateway only)');
		$style->table(['ID', 'Type', 'Name', 'Device type', 'Interface', 'Bus pin', 'Pgm pin', 'Power pin', 'Baud rate', 'I2C pin*', 'SPI pin*', 'UART pin*'], $mappings);
		return 0;
	}

}
