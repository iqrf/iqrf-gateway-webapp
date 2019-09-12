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
use Iqrf\Repository\Models\FilesManager;
use Iqrf\Repository\Models\OsAndDpaManager;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\SmartObject;
use Nette\Utils\FileSystem;
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
	 * @var EnumerationManager IQMESH enumeration manager
	 */
	private $enumerationManager;

	/**
	 * @var FilesManager Files manager
	 */
	private $filesManager;

	/**
	 * @var OsAndDpaManager IQRF Repository OS&DPA manager
	 */
	private $osDpaManager;

	/**
	 * Constructor
	 * @param Context $database Database context
	 * @param EnumerationManager $enumeration IQMESH enumeration manager
	 * @param OsAndDpaManager $osDpaManager IQRF Repository OS&DPA manager
	 * @param FilesManager $filesManager Files manager
	 */
	public function __construct(Context $database, EnumerationManager $enumeration, OsAndDpaManager $osDpaManager, FilesManager $filesManager) {
		$this->database = $database;
		$this->enumerationManager = $enumeration;
		$this->osDpaManager = $osDpaManager;
		$this->filesManager = $filesManager;
	}

	/**
	 * Lists available IQRF OS changes
	 * @return string[] Available IQRF OS changes
	 */
	public function list(): array {
		$array = [];
		$enumeration = $this->enumerationManager->device(0);
		$osVersions = $this->listOsVersions($enumeration);
		foreach ($osVersions as $osVersion => $os) {
			$dpaVersions = $this->osDpaManager->get($osVersion);
			foreach ($dpaVersions as $dpa) {
				if (hexdec($dpa->getDpa()) < 0x400) {
					$array[$osVersion . ',' . $dpa->getDpa() . ',LP'] = $os . ', DPA ' . $dpa->getDpa(true) . ', LP RF mode';
					$array[$osVersion . ',' . $dpa->getDpa() . ',STD'] = $os . ', DPA ' . $dpa->getDpa(true) . ', STD RF mode';
				} else {
					$array[$osVersion . ',' . $dpa->getDpa()] = $os . ', DPA ' . $dpa->getDpa(true);
				}
			}
		}
		krsort($array);
		return $array;
	}

	/**
	 * Lists IQRF OS versions
	 * @param mixed[] $enumeration IQMESH device enumeration
	 * @return array<string,string> IQRF OS versions
	 */
	private function listOsVersions(array $enumeration): array {
		$osVersions = [];
		$osBuild = $enumeration['response']['data']['rsp']['osRead']['osBuild'];
		$osVersion = $enumeration['response']['data']['rsp']['osRead']['osVersion'];
		$osVersions[$osBuild] = 'IQRF OS ' . $osVersion . ' (' . $osBuild . ')';
		$table = $this->database->table('os_patches');
		$table->where('from_build = ? AND part = ?', $osBuild, 1);
		/**
		 * @var ActiveRow $row Database active row
		 */
		foreach ($table->fetchAll() as $row) {
			$os = $row->toArray();
			$versionInt = hexdec($os['to_version']);
			$version = strval($versionInt >> 8) . '.';
			$version .= Strings::padLeft(dechex($versionInt & 0xff), 2, '0') . 'D';
			$osVersions[$os['to_build']] = 'IQRF OS ' . $version . ' (' . $os['to_build'] . ')';
		}
		return $osVersions;
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
		$enumeration = $this->enumerationManager->device(0)['response']['data']['rsp'];
		$fromOs = $enumeration['osRead']['osBuild'];
		$files = $this->getOsFiles($fromOs, $os);
		$trSeries = TrSeries::fromTrType($enumeration['osRead']['trMcuType']['trType']);
		$files[] = $this->getDpaFile($os, $dpa, $trSeries, $rfMode);
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
			return __DIR__ . '/../../../iqrf/os/' . $row->toArray()['filename'];
		}, $table->fetchAll());
	}

	/**
	 * Returns DPA file name
	 * @param string $osBuild IQRF OS build number
	 * @param string $dpaVersion DPA version
	 * @param TrSeries $trSeries TR series
	 * @param string|null $rfMode RF mode
	 * @return string|null DPA file name
	 */
	public function getDpaFile(string $osBuild, string $dpaVersion, TrSeries $trSeries, ?string $rfMode = null): ?string {
		$path = $this->osDpaManager->get($osBuild, $dpaVersion)[0]->getDownloadPath();
		$this->filesManager->setPath($path);
		$files = $this->filesManager->list()->getFiles();
		$filePrefixes = [
			'./GeneralHWP-Coordinator-' . $rfMode . '-SPI-' . $trSeries->toScalar() . '-',
			'./HWP-Coordinator-' . $rfMode . '-SPI-' . $trSeries->toScalar() . '-',
			'./DPA-Coordinator-SPI-' . $trSeries->toScalar() . '-',
		];
		foreach ($files as $file) {
			foreach ($filePrefixes as $filePrefix) {
				if (Strings::startsWith($file->getName(), $filePrefix)) {
					$fileContent = $this->filesManager->download($file->getName());
					$filePath = '/tmp/iqrf-gateway-daemon/' . $file->getName();
					FileSystem::write($filePath, $fileContent);
					return $filePath;
				}
			}
		}
		return null;
	}

}
