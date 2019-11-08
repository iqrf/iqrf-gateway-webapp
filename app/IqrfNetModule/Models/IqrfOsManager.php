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

use App\IqrfNetModule\Enums\TrSeries;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\SmartObject;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;

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
	 * @var EnumerationManager IQMESH enumeration manager
	 */
	private $enumerationManager;

	/**
	 * Constructor
	 * @param Context $database Database context
	 * @param DpaManager $dpaManager DPA manager
	 * @param EnumerationManager $enumeration IQMESH enumeration manager
	 */
	public function __construct(Context $database, DpaManager $dpaManager, EnumerationManager $enumeration) {
		$this->database = $database;
		$this->dpaManager = $dpaManager;
		$this->enumerationManager = $enumeration;
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
		$enumeration = $this->enumerationManager->device(0);
		$build = $enumeration['response']['data']['rsp']['osRead']['osBuild'];
		$version = $enumeration['response']['data']['rsp']['osRead']['osVersion'];
		$osVersions = $this->listVersions($build, $version);
		foreach ($osVersions as $osVersion => $osBuild) {
			foreach ($this->dpaManager->list($osVersion) as $dpa) {
				$index = $osVersion . ',' . $dpa->getDpa();
				$version = $osBuild . ', DPA ' . $dpa->getDpa(true);
				if (hexdec($dpa->getDpa()) < 0x400) {
					$array[$index . ',LP'] = $version . ', LP RF mode';
					$array[$index . ',STD'] = $version . ', STD RF mode';
				} else {
					$array[$index] = $version;
				}
			}
		}
		krsort($array);
		return $array;
	}

	/**
	 * Lists IQRF OS versions
	 * @param string $build Current IQRF OS build
	 * @param string $version Current IQRF OS version
	 * @return array<string,string> IQRF OS versions
	 */
	private function listVersions(string $build, string $version): array {
		$versions = [];
		$versions[$build] = 'IQRF OS ' . $version . ' (' . $build . ')';
		$table = $this->database->table('os_patches');
		$table->where('from_build = ? AND part = ?', $build, 1);
		/**
		 * @var ActiveRow $os Database active row
		 */
		foreach ($table->fetchAll() as $os) {
			$osVersion = $this->getPrettyVersion((string) $os->to_version);
			$versions[$os->to_build] = 'IQRF OS ' . $osVersion . ' (' . $os->to_build . ')';
		}
		return $versions;
	}

	/**
	 * Returns files to upload
	 * @param string $os IQRF OS build
	 * @param string $dpa DPA version
	 * @param string|null $rfMode RF mode
	 * @return string[] Files to upload
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws UserErrorException
	 * @throws JsonException
	 */
	public function getFiles(string $os, string $dpa, ?string $rfMode = null): array {
		$osRead = $this->enumerationManager->device(0)['response']['data']['rsp']['osRead'];
		$files = $this->getOsFiles($osRead['osBuild'], $os);
		$trSeries = TrSeries::fromTrType($osRead['trMcuType']['trType']);
		$files[] = $this->dpaManager->getFile($os, $dpa, $trSeries, $rfMode);
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

	/**
	 * @param string $osVersion IQRF OS version
	 * @return string Pretty formatted IQRF OS version
	 */
	private function getPrettyVersion(string $osVersion): string {
		$versionInt = hexdec($osVersion);
		$version = strval($versionInt >> 8) . '.';
		$version .= Strings::padLeft(dechex($versionInt & 0xff), 2, '0') . 'D';
		return $version;
	}

}
