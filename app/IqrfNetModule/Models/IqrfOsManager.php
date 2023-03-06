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
use stdClass;

/**
 * IQRF OS manager
 */
class IqrfOsManager {

	/**
	 * @var IqrfOsPatchRepository IQRF OS patch database repository
	 */
	private readonly IqrfOsPatchRepository $repository;

	/**
	 * Constructor
	 * @param DpaManager $dpaManager DPA manager
	 * @param EntityManager $entityManager Entity manager
	 * @param OsAndDpaManager $osDpaManager IQRF OS and DPA manager
	 * @param UploadManager $uploadManager Upload manager
	 */
	public function __construct(
		private readonly DpaManager $dpaManager,
		EntityManager $entityManager,
		private readonly OsAndDpaManager $osDpaManager,
		private readonly UploadManager $uploadManager,
	) {
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
	 * Finds OS and DPA upgrade files and executes upgrade utilizing the IQRF Gateway Uploader utility
	 * @param stdClass $params OS and DPA upgrade parameters
	 */
	public function upgradeOs(stdClass $params): void {
		$files = [
			'os' => $this->getOsUpgrade($params),
			'dpa' => $this->getDpaUpgrade($params),
		];
		foreach ($files['os'] as $file) {
			$this->uploadManager->uploadToTr($file, true);
			sleep(5);
		}
		$this->uploadManager->uploadToTr($files['dpa']);
	}

	/**
	 * Retrieves OS upgrade files
	 * @param stdClass $params OS upgrade parameters
	 * @return array<int, string> OS upgrade file(s)
	 */
	private function getOsUpgrade(stdClass $params): array {
		$patches = $this->repository->findBy([
			'fromBuild' => hexdec($params->fromBuild),
			'toBuild' => hexdec($params->toBuild),
		]);
		$osUpgrades = array_map(static fn (IqrfOsPatch $patch): string => $patch->getFileName(), $patches);
		sort($osUpgrades);
		return $osUpgrades;
	}

	/**
	 * Retrieves DPA file name for upgrade
	 * @param stdClass $params DPA upgrade parameters
	 * @return string DPA upgrade file name
	 * @throws DpaFileNotFoundException
	 * @throws DpaRfMissingException
	 */
	private function getDpaUpgrade(stdClass $params): string {
		$iface = DpaInterfaces::fromScalar($params->interface);
		$trSeries = TrSeries::fromTrMcuType($params->trMcuType);
		$rfMode = isset($params->rfMode) ? RfModes::fromScalar($params->rfMode) : null;
		$dpa = new Dpa($params->dpa, $iface, $trSeries, $rfMode);
		if (hexdec($dpa->getVersion()) < 0x400 && $rfMode === null) {
			throw new DpaRfMissingException('Missing RF mode for DPA version older than 4.00');
		}
		$fileName = $this->dpaManager->getFile($params->toBuild, $dpa);
		if ($fileName === null) {
			throw new DpaFileNotFoundException('No DPA file matched the metadata');
		}
		return $fileName;
	}

}
