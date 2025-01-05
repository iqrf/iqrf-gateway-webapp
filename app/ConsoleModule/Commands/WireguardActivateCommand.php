<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * WireGuard activate command
 */
class WireguardActivateCommand extends WireguardCommand {

	/**
	 * @var string|null Command name
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected static $defaultName = 'wireguard:activate';

	/**
	 * Configures the Wireguard activate command
	 */
	protected function configure(): void {
		$this->setDescription('Activates a WireGuard tunnel');
		$this->addArgument('name', InputArgument::OPTIONAL, 'Name of WireGuard tunnel to activate');
	}

	/**
	 * Executes the Wireguard activate command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$style = new SymfonyStyle($input, $output);
		$style->title('Activate WireGuard tunnel');
		$tunnelName = $input->getArgument('name');
		if ($tunnelName === null) {
			$style->error('No WireGuard tunnel specified.');
			return Command::FAILURE;
		}
		$tunnel = $this->repository->findInterfaceByName($tunnelName);
		if ($tunnel === null) {
			$style->error('WireGuard tunnel ' . $tunnelName . ' does not exist.');
			return Command::FAILURE;
		}
		if ($this->manager->isTunnelActive($tunnel)) {
			$style->block('WireGuard tunnel ' . $tunnelName . ' is already active.', 'INFO', 'fg=white;bg=blue', ' ', true);
			return Command::SUCCESS;
		}
		try {
			$this->manager->initializeTunnel($tunnel);
		} catch (Throwable $e) {
			$this->manager->deleteTunnel($tunnel);
			$style->error($e->getMessage());
			return Command::FAILURE;
		}
		$style->success('WireGuard tunnel ' . $tunnelName . ' has been activated.');
		return Command::SUCCESS;
	}

}
