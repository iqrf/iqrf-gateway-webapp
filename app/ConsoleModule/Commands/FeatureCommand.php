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

use App\ConsoleModule\Models\FeatureManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Feature base command
 */
abstract class FeatureCommand extends Command {

	/**
	 * Constructor
	 * @param FeatureManager $manager Webapp's optional features manager
	 */
	public function __construct(
		protected readonly FeatureManager $manager,
	) {
		parent::__construct();
	}

	/**
	 * Clear cache
	 * @param OutputInterface $output Command output
	 */
	protected function clearCache(OutputInterface $output): void {
		$input = new ArrayInput([], new InputDefinition([
			new InputOption('recreate', null, InputOption::VALUE_OPTIONAL, 'Recreate folders', false),
		]));
		$this->getApplication()->find('nette:cache:purge')->execute($input, $output);
	}

}
