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

use App\IqrfNetModule\Enums\TrSeries;
use App\Models\Database\Entities\IqrfOsPatch;
use App\Models\Database\EntityManager;
use DomainException;
use Nette\Utils\Finder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * IQRF OS patch import command
 */
#[AsCommand(name: 'iqrf-os:import-patches', description: 'Imports IQRF OS patches')]
class IqrfOsPatchImportCommand extends Command {

	/**
	 * Path to directory with IQRF OS patches
	 */
	private const DIR_PATH = __DIR__ . '/../../../iqrf/os/';

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 */
	public function __construct(
		private readonly EntityManager $entityManager,
	) {
		parent::__construct();
	}

	/**
	 * Executes the IQRF OS patch import command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$repository = $this->entityManager->getIqrfOsPatchRepository();
		foreach ($repository->findAll() as $patch) {
			if (file_exists(self::DIR_PATH . $patch->fileName)) {
				continue;
			}
			$output->writeln('Deleting IQRF OS patch' . $patch->fileName);
			$this->entityManager->remove($patch);
			$this->entityManager->flush();
		}
		foreach (Finder::findFiles('*.iqrf')->in(self::DIR_PATH) as $file) {
			$fileName = $file->getFilename();
			if ($repository->findOneBy(['fileName' => $fileName]) !== null) {
				$output->writeln('IQRF OS patch ' . $file->getFilename() . ' has been already imported.');
				continue;
			}
			$output->writeln('Processing IQRF OS patch ' . $file->getFilename());
			$array = explode('-', $file->getBasename('.iqrf'));
			[$fromVersion, $fromBuild] = sscanf($array[2], '%3d(%4x)');
			[$toVersion, $toBuild] = sscanf($array[3], '%3d(%4x)');
			if (count($array) === 5) {
				$partArr = explode('of', $array[4]);
				$part = (int) $partArr[0];
				$parts = (int) $partArr[1];
			} else {
				$parts = $part = 1;
			}
			try {
				TrSeries::fromIqrfOsFileName($array[1]);
			} catch (DomainException $e) {
				$output->writeln(sprintf('Failed to import IQRF OS patch %s: %s', $file->getFilename(), $e->getMessage()));
				continue;
			}
			$patch = new IqrfOsPatch($array[1], $fromVersion, $fromBuild, $toVersion, $toBuild, $part, $parts, $fileName);
			$output->writeln('Importing IQRF OS patch ' . $file->getFilename());
			$this->entityManager->persist($patch);
			$this->entityManager->flush();
		}
		return 0;
	}

}
