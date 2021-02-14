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
use Exception;
use Nette\Utils\FileSystem;
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
	 * @var string Command name
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
		$command = $this->commandManager->run('wg show ' . $tunnel->getName(), true);
		if ($command->getExitCode() === 0) {
			$style->block('WireGuard tunnel ' . $tunnelName . ' is already active.', 'INFO', 'fg=white;bg=blue', ' ', true);
			return Command::SUCCESS;
		}
		try {
			$this->up($tunnel);
		} catch (Throwable $e) {
			$this->commandManager->run('ip link delete dev ' . $tunnel->getName(), true);
			$style->error($e->getMessage());
			return Command::FAILURE;
		}
		$style->success('WireGuard tunnel ' . $tunnelName . ' has been activated.');
		return Command::SUCCESS;
	}

	/**
	 * Configures Wireguard interface and peers
	 * @param WireguardInterface $iface Wireguard interface entity
	 */
	private function up(WireguardInterface $iface): void {
		$name = $iface->getName();
		$output = $this->commandManager->run('ip link add ' . $name . ' type wireguard', true);
		if ($output->getExitCode() !== 0) {
			throw new Exception('Failed to create new interface: ' . $output->getStderr() . '.');
		}
		$output = $this->commandManager->run($iface->wgSerialize(), true);
		if ($output->getExitCode() !== 0) {
			throw new Exception('Failed to set wg tunnel properties: ' . $output->getStderr() . '.');
		}
		$pkFile = '/etc/wireguard' . $name . '.privatekey';
		FileSystem::write($pkFile, $iface->getPrivateKey());
		$command = sprintf('wg set %s private-key %s', $name, $pkFile);
		$output = $this->commandManager->run($command, true);
		if ($output->getExitCode() !== 0) {
			throw new Exception('Failed to set interface private key: ' . $output->getStderr());
		}
		FileSystem::delete($pkFile);
		if ($iface->getIpv4() !== null) {
			$command = sprintf('ip -4 address add %s dev %s', $iface->getIpv4()->toString(), $name);
			$output = $this->commandManager->run($command, true);
			if ($output->getExitCode() !== 0) {
				throw new Exception('Failed to set interface IPv4 address: ' . $output->getStderr() . '.');
			}
		}
		if ($iface->getIpv6() !== null) {
			$command = sprintf('ip -6 address add %s dev %s', $iface->getIpv6()->toString(), $name);
			$output = $this->commandManager->run($command, true);
			if ($output->getExitCode() !== 0) {
				throw new Exception('Failed to set interface IPv6 address: ' . $output->getStderr() . '.');
			}
		}
		$output = $this->commandManager->run('ip link set mtu 1420 up dev ' . $name, true);
		if ($output->getExitCode() !== 0) {
			throw new Exception('Failed to set interface MTU: ' . $output->getStderr() . '.');
		}
		foreach ($iface->getPeers()->toArray() as $peer) {
			$addresses = [];
			foreach ($peer->getAddresses()->toArray() as $addr) {
				if ($addr->getAddress()->getVersion() === 6) {
					$addresses[] = $addr->getAddress()->toString();
				}
			}
			foreach ($addresses as $addr) {
				$command = sprintf('ip -6 route add %s dev %s', $addr, $name);
				$output = $this->commandManager->run($command, true);
				if ($output->getExitCode() !== 0) {
					throw new Exception('Failed to set IPv6 route: ' . $output->getStderr() . '.');
				}
			}
		}
	}

}
