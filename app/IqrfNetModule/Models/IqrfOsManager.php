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

namespace App\IqrfNetModule\Models;

use App\IqrfNetModule\Entities\Dpa;
use App\IqrfNetModule\Entities\IqrfOs;
use App\IqrfNetModule\Enums\TrSeries;
use App\Models\Database\Entities\IqrfOsPatch;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\IqrfOsPatchRepository;

/**
 * IQRF OS manager
 */
class IqrfOsManager {

	/**
	 * @var DpaManager DPA manager
	 */
	private $dpaManager;

	/**
	 * @var IqrfOsPatchRepository IQRF OS patch database repository
	 */
	private $repository;

	/**
	 * Constructor
	 * @param DpaManager $dpaManager DPA manager
	 * @param EntityManager $entityManager Entity manager
	 */
	public function __construct(DpaManager $dpaManager, EntityManager $entityManager) {
		$this->dpaManager = $dpaManager;
		$this->repository = $entityManager->getIqrfOsPatchRepository();
	}

	/**
	 * Lists available IQRF OS changes
	 * @param IqrfOs $os Current IQRF OS entity
	 * @return array<string> Available IQRF OS changes
	 */
	public function list(IqrfOs $os): array {
		$array = [];
		$osVersions = $this->listVersions($os);
		foreach ($osVersions as $osBuild => $osVersion) {
			foreach ($this->dpaManager->list($osBuild) as $dpa) {
				$index = $osBuild . ',' . $dpa->getDpa();
				$versions = $osVersion . ', DPA ' . $dpa->getDpa(true);
				if (hexdec($dpa->getDpa()) < 0x400) {
					$array[$index . ',LP'] = $versions . ', LP RF mode';
					$array[$index . ',STD'] = $versions . ', STD RF mode';
				} else {
					$array[$index] = $versions;
				}
			}
		}
		krsort($array);
		return $array;
	}

	/**
	 * Lists IQRF OS versions
	 * @param IqrfOs $os Current IQRF OS
	 * @return array<string,string> IQRF OS versions
	 */
	private function listVersions(IqrfOs $os): array {
		$versions = [];
		$versions[$os->getBuild()] = $os->getDescription();
		$patches = $this->repository->findBy(['fromBuild' => $os->getBuild(), 'part' => 1]);
		foreach ($patches as $patch) {
			assert($patch instanceof IqrfOsPatch);
			$trType = TrSeries::fromIqrfOsFileName($patch->getModuleType());
			$toBuild = dechex($patch->getToBuild());
			$entity = new IqrfOs($toBuild, (string) $patch->getToVersion(), $trType);
			$versions[$toBuild] = $entity->getDescription();
		}
		return $versions;
	}

	/**
	 * Returns files to upload
	 * @param string $fromBuild Current IQRF OS build
	 * @param string $toBuild Target IQRF OS build
	 * @param Dpa $dpa DPA entity
	 * @return array<string> Files to upload
	 */
	public function getFiles(string $fromBuild, string $toBuild, Dpa $dpa): array {
		$files = $this->getOsFiles($fromBuild, $toBuild);
		$files[] = $this->dpaManager->getFile($toBuild, $dpa);
		return $files;
	}

	/**
	 * Returns IQRF OS diff files
	 * @param string $fromBuild From IQRF OS build
	 * @param string $toBuild To IQRF OS build
	 * @return array<string> Array of IQRF OS diff files
	 */
	public function getOsFiles(string $fromBuild, string $toBuild): array {
		$patches = $this->repository->findBy([
			'fromBuild' => hexdec($fromBuild),
			'toBuild' => hexdec($toBuild),
		], ['part' => 'ASC']);
		return array_map(function (IqrfOsPatch $patch): string {
			return __DIR__ . '/../../../iqrf/os/' . $patch->getFileName();
		}, $patches);
	}

}
