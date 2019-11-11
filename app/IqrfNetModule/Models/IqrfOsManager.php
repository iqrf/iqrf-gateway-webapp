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

use App\IqrfNetModule\Entities\IqrfOs;
use App\IqrfNetModule\Enums\TrSeries;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * IQRF OS manager
 */
class IqrfOsManager {

	use SmartObject;

	/**
	 * @var Context Database context
	 */
	private $database;

	/**
	 * @var DpaManager DPA manager
	 */
	private $dpaManager;

	/**
	 * @var OsManager DPA OS peripheral manager
	 */
	private $osManager;

	/**
	 * Constructor
	 * @param Context $database Database context
	 * @param DpaManager $dpaManager DPA manager
	 * @param OsManager $osManager DPA OS peripheral manager
	 */
	public function __construct(Context $database, DpaManager $dpaManager, OsManager $osManager) {
		$this->database = $database;
		$this->dpaManager = $dpaManager;
		$this->osManager = $osManager;
	}

	/**
	 * Returns IQRF OS entity
	 * @return IqrfOs Current IQRF OS entity
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	private function getCurrent(): IqrfOs {
		$osRead = $this->osManager->read(0);
		return IqrfOs::fromOsRead($osRead);
	}

	/**
	 * Lists available IQRF OS changes
	 * @return string[] Available IQRF OS changes
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	public function list(): array {
		$array = [];
		$os = $this->getCurrent();
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
		$table = $this->database->table('os_patches');
		$table->where('from_build = ? AND part = ?', $os->getBuild(), 1);
		/**
		 * @var ActiveRow $row Database active row
		 */
		foreach ($table->fetchAll() as $row) {
			$trType = TrSeries::fromScalar($row->module_type);
			$entity = new IqrfOs((string) $row->to_build, (string) $row->to_version, $trType);
			$versions[$row->to_build] = $entity->getDescription();
		}
		return $versions;
	}

	/**
	 * Returns files to upload
	 * @param string $build IQRF OS build
	 * @param string $dpa DPA version
	 * @param string|null $rfMode RF mode
	 * @return string[] Files to upload
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws UserErrorException
	 * @throws JsonException
	 */
	public function getFiles(string $build, string $dpa, ?string $rfMode = null): array {
		$os = $this->getCurrent();
		$files = $this->getOsFiles($os->getBuild(), $build);
		$files[] = $this->dpaManager->getFile($build, $dpa, $os->getTrSeries(), $rfMode);
		return $files;
	}

	/**
	 * Returns IQRF OS diff files
	 * @param string $fromBuild From IQRF OS build
	 * @param string $toBuild To IQRF OS build
	 * @return string[] Array of IQRF OS diff files
	 */
	public function getOsFiles(string $fromBuild, string $toBuild): array {
		$table = $this->database->table('os_patches');
		$table->select('filename');
		$table->where('from_build = ? AND to_build = ?', $fromBuild, $toBuild);
		$table->order('part ASC');
		return array_map(function (ActiveRow $row): string {
			return __DIR__ . '/../../../iqrf/os/' . $row->filename;
		}, $table->fetchAll());
	}

}
