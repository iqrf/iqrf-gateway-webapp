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

use App\ConsoleModule\Models\FeatureManager;
use Nette\Neon\Exception;
use Nette\SmartObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CLI command for listing features
 */
class FeatureListCommand extends Command {

	use SmartObject;

	/**
	 * @var string Command name
	 */
	protected static $defaultName = 'feature:list';

	/**
	 * @var FeatureManager Webapp's optional feature manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param FeatureManager $manager Webapp's optional feature manager
	 */
	public function __construct(FeatureManager $manager) {
		$this->manager = $manager;
		parent::__construct();
	}

	/**
	 * Configures the user list command
	 */
	protected function configure(): void {
		$this->setName(self::$defaultName);
		$this->setDescription('Lists webapp\'s features');
	}

	/**
	 * Executes the features list command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 */
	protected function execute(InputInterface $input, OutputInterface $output): void {
		$header = ['Full name', 'Name', 'Status'];
		$style = new SymfonyStyle($input, $output);
		$style->title('Feature list');
		try {
			$features = $this->manager->list();
			$style->table($header, $features);
		} catch (Exception $e) {
			$style->error('An error occurred while reading configuration file.');
		}
	}

}
