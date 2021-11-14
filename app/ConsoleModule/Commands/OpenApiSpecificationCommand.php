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

use App\ApiModule\Version0\Models\OpenApiSchemaBuilder;
use Nette\Utils\Json;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * OpenAPI specification command
 */
class OpenApiSpecificationCommand extends Command {

	/**
	 * @var OpenApiSchemaBuilder OpenAPI schema builder
	 */
	private $schemaBuilder;

	/**
	 * @var string Command name
	 */
	protected static $defaultName = 'open-api:specification';

	/**
	 * Constructior
	 * @param string|null $name Command name
	 */
	public function __construct(OpenApiSchemaBuilder $schemaBuilder, ?string $name = null) {
		$this->schemaBuilder = $schemaBuilder;
		parent::__construct($name);
	}

	/**
	 * Configures the OpenAPI specification command
	 */
	protected function configure(): void {
		$this->setDescription('Outputs OpenAPI specification');
	}

	/**
	 * Executes the OpenAPI specification command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$schema = $this->schemaBuilder->getArray();
		$output->writeln(Json::encode($schema));
		return 0;
	}

}
