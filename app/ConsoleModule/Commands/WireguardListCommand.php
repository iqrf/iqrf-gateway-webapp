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

use App\Models\Database\Entities\WireguardInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function assert;

/**
 * CLI command to list WireGuard tunnels
 */
class WireguardListCommand extends WireguardCommand {

	/**
	 * @var string Command name
	 */
	protected static $defaultName = 'wireguard:list';

	/**
	 * Configures the WireGuard list command
	 */
	protected function configure(): void {
		$this->setDescription('Lists WireGuard tunnels');
	}

	/**
	 * Executes the WireGuard list key list command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$tunnels = [];
		foreach ($this->repository->findAll() as $tunnel) {
			assert($tunnel instanceof WireguardInterface);
			$command = $this->commandManager->run('wg show ' . $tunnel->getName(), true);
			$state = $command->getExitCode() === 0 ? 'active' : 'inactive';
			$tunnels[] = [$tunnel->getName(), $state];
		}
		$style = new SymfonyStyle($input, $output);
		$style->title('List of WireGuard tunnels');
		$style->table(['Name', 'State'], $tunnels);
		return Command::SUCCESS;
	}

}
