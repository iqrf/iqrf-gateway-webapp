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

use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\IqrfOsPatchRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'iqrf-os:list-patches', description: 'Lists IQRF OS patches')]
class IqrfOsPatchListCommand extends EntityManagerCommand {

	/**
	 * @var IqrfOsPatchRepository IQRF OS Patch database repository
	 */
	protected readonly IqrfOsPatchRepository $repository;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 */
	public function __construct(EntityManager $entityManager) {
		parent::__construct($entityManager);
		$this->repository = $this->entityManager->getIqrfOsPatchRepository();
	}

	/**
	 * Executes the IQRF OS patch list command
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$patches = $this->repository->getOsPatchDetails();
		$style = new SymfonyStyle($input, $output);
		$style->title('IQRF OS patches');
		$style->table(['ID', 'Module Type', 'From DPA Version', 'From OS Build', 'To DPA Version', 'To OS Build', 'Part Number', 'Parts', 'File Name'], $patches);
		return 0;
	}

}
