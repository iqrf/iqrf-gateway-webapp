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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class DatabaseCreateCommand extends Command {

	/**
	 * @var string|null Command name
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected static $defaultName = 'database:create';

	/**
	 * Constructor
	 * @param ManagerRegistry $managerRegistry Manager registry
	 * @param string|null $name Command name
	 */
	public function __construct(
		private readonly ManagerRegistry $managerRegistry,
		?string $name = null,
	) {
		parent::__construct($name);
	}

	/**
	 * Configures the database create command
	 */
	protected function configure(): void {
		$this->setDescription('Creates webapp\'s database');
	}

	/**
	 * Executes the database create command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$connectionName = $this->managerRegistry->getDefaultConnectionName();
		$connection = $this->managerRegistry->getConnection($connectionName);
		assert($connection instanceof Connection);
		$params = $connection->getParams();
		$tmpConnection = DriverManager::getConnection($params);
		$name = $params['path'] ?? ($params['dbname'] ?? false);
		if ($name === false) {
			$output->writeln('<error>Connection does not contain a \'path\' or \'dbname\' parameter and cannot be created.</error>');
			return 1;
		}
		try {
			$tmpConnection->createSchemaManager()->createDatabase($name);
			$output->writeln(sprintf('<info>Created database <comment>%s</comment> for connection named <comment>%s</comment></info>', $name, $connectionName));
		} catch (Throwable $e) {
			$output->writeln(sprintf('<error>Could not create database <comment>%s</comment> for connection named <comment>%s</comment></error>', $name, $connectionName));
			$output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
			$tmpConnection->close();
			return 1;
		}
		$tmpConnection->close();
		return 0;
	}

}
