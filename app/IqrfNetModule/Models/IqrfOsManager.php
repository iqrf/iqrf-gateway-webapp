<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
use App\IqrfNetModule\Enums\DpaInterfaces;
use App\IqrfNetModule\Enums\RfModes;
use App\IqrfNetModule\Enums\TrSeries;
use App\IqrfNetModule\Exceptions\DpaFileNotFoundException;
use App\IqrfNetModule\Exceptions\DpaRfMissingException;
use App\Models\Database\Entities\IqrfOsPatch;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\IqrfOsPatchRepository;
use Iqrf\Repository\Models\OsAndDpaManager;

/**
 * IQRF OS manager
 */
class IqrfOsManager {

	/**
	 * @var DpaManager DPA manager
	 */
	private DpaManager $dpaManager;

	/**
	 * @var OsAndDpaManager IQRF OS and DPA manager
	 */
	private OsAndDpaManager $osDpaManager;

	/**
	 * @var IqrfOsPatchRepository IQRF OS patch database repository
	 */
	private IqrfOsPatchRepository $repository;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 */
	public function __construct(DpaManager $dpaManager, OsAndDpaManager $osDpaManager, EntityManager $entityManager) {
		$this->dpaManager = $dpaManager;
		$this->osDpaManager = $osDpaManager;
		$this->repository = $entityManager->getIqrfOsPatchRepository();
	}

	/**
	 * Lists all IQRF OS patches
	 * @return array<int, array<string, int|string>> IQRF OS patches
	 */
	public function listOsPatches(): array {
		$patches = [];
		foreach ($this->repository->findAll() as $patch) {
			$patches[] = $patch->jsonSerialize();
		}
		return $patches;
	}

	/**
	 * Lists available IQRF OS upgrades
	 * @param string $build Current build of IQRF OS
	 * @param int $mcuType Module MCU type
	 * @return array<int, array<string, int|string>> Available IQRF OS upgrades
	 */
	public function listOsUpgrades(string $build, int $mcuType): array {
		$versions = [];
		if ($mcuType !== 4) {
			return $versions;
		}
		$currentBuild = hexdec($build);
		$patches = $this->repository->findBy(['fromBuild' => $currentBuild, 'part' => 1]);
		foreach ($patches as $patch) {
			$toBuild = $patch->getToBuild();
			if ($toBuild <= $currentBuild) {
				continue;
			}
			foreach ($this->osDpaManager->get(str_pad(dechex($toBuild), 4, '0', STR_PAD_LEFT)) as $dpa) {
				$upgrade = $dpa->jsonSerialize();
				if (hexdec($dpa->getDpa()->getVersion()) < 0x400) {
					$upgrade['dpa'] = $dpa->getDpa()->jsonSerialize();
					$upgrade['dpa']['rfMode'] = 'LP';
					$versions[] = $upgrade;
					$upgrade['dpa']['rfMode'] = 'STD';
				}
				$versions[] = $upgrade;
			}
		}
		return $versions;
	}

	/**
	 * Retrieves names of files to be used in IQRF OS upgrade
	 * @param array<string, int|string> $request API request body
	 * @return array{dpa: string, os: array<string>} Array containing names of files to be used in upgrade
	 * @throws DpaFileNotFoundException
	 * @throws DpaRfMissingException
	 */
	public function getUpgradeFiles(array $request): array {
		$dpaFile = $this->getDpaFileName($request);
		$osFiles = $this->getOsFileNames($request);
		return ['dpa' => $dpaFile, 'os' => $osFiles];
	}

	/**
	 * Retrieves DPA file name for upgrade
	 * @param array<string, int|string> $request API request body
	 * @return string Name of DPA file
	 * @throws DpaFileNotFoundException
	 * @throws DpaRfMissingException
	 */
	private function getDpaFileName(array $request): string {
		$iface = DpaInterfaces::fromScalar($request['interface']);
		$trSeries = TrSeries::fromTrMcuType($request['trMcuType']);
		$rfMode = isset($request['rfMode']) ? RfModes::fromScalar($request['rfMode']) : null;
		$dpa = new Dpa($request['dpa'], $iface, $trSeries, $rfMode);
		if (hexdec($dpa->getVersion()) < 0x400 && $rfMode === null) {
			throw new DpaRfMissingException('Missing RF mode for DPA version older than 4.00');
		}
		$fileName = $this->dpaManager->getFile($request['toBuild'], $dpa);
		if ($fileName === null) {
			throw new DpaFileNotFoundException('No DPA file matched the metadata');
		}
		return $fileName;
	}

	/**
	 * Retrieves OS file names for upgrade
	 * @param array<string, int|string> $request API request body
	 * @return array<int, string> Name of DPA file
	 */
	private function getOsFileNames(array $request): array {
		$patches = $this->repository->findBy([
			'fromBuild' => hexdec($request['fromBuild']),
			'toBuild' => hexdec($request['toBuild']),
			]);
		return array_map(function (IqrfOsPatch $patch): string {
			return __DIR__ . '/../../../iqrf/os/' . $patch->getFileName();
		}, $patches);
	}

}
