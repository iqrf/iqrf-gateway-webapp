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

use App\ConsoleModule\Exceptions\UnknownFeatureException;
use App\ConsoleModule\Models\FeatureManager;
use Nette\IOException;
use Nette\Neon\Exception as NeonException;
use Nette\SmartObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CLI command for enabling features
 */
class FeatureEnableCommand extends Command {

	use SmartObject;

	/**
	 * @var string Command name
	 */
	protected static $defaultName = 'feature:enable';

	/**
	 * @var FeatureManager Webapp's optional features manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param FeatureManager $manager Webapp's optional features manager
	 */
	public function __construct(FeatureManager $manager) {
		$this->manager = $manager;
		parent::__construct();
	}

	/**
	 * Configures the feature enable command
	 */
	protected function configure(): void {
		$this->setName(self::$defaultName);
		$this->setDescription('Enables webapp\'s features');
		$this->addArgument('names', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Names of enabled features');
	}

	/**
	 * Executes the feature enable command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 */
	protected function execute(InputInterface $input, OutputInterface $output): void {
		$style = new SymfonyStyle($input, $output);
		$style->title('Enable features');
		$names = $input->getArgument('names');
		try {
			$this->manager->enable($names);
		} catch (IOException | NeonException $e) {
			$style->error('An error occurred while enabling features.');
		} catch (UnknownFeatureException $e) {
			$style->error('Unknown feature ' . $e->getMessage() . '.');
		}
		if (count($names) === 1) {
			$style->success(sprintf('Feature %s has been successfully enabled.', $names[0]));
		} else {
			$style->success(sprintf('Features %s have been successfully enabled.', implode(', ', $names)));
		}
		$this->getApplication()->find('nette:cache:purge')->execute($input, $output);
	}

}
