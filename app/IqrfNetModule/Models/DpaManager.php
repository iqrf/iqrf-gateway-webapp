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
use Iqrf\Repository\Entities\OsDpa;
use Iqrf\Repository\Models\FilesManager;
use Iqrf\Repository\Models\OsAndDpaManager;

/**
 * DPA manager
 */
class DpaManager {

	/**
	 * @var FilesManager Files manager
	 */
	private FilesManager $filesManager;

	/**
	 * @var OsAndDpaManager IQRF Repository OS&DPA manager
	 */
	private OsAndDpaManager $osDpaManager;

	/**
	 * @var UploadManager Upload manager
	 */
	private UploadManager $uploadManager;

	/**
	 * Constructor
	 * @param OsAndDpaManager $osDpaManager IQRF Repository OS&DPA manager
	 * @param FilesManager $filesManager Files manager
	 * @param UploadManager $uploadManager Upload manager
	 */
	public function __construct(OsAndDpaManager $osDpaManager, FilesManager $filesManager, UploadManager $uploadManager) {
		$this->osDpaManager = $osDpaManager;
		$this->filesManager = $filesManager;
		$this->uploadManager = $uploadManager;
	}

	/**
	 * Lists DPA versions for IQRF OS build
	 * @param string $osBuild IQRF OS build
	 * @return array<OsDpa> IQRF OS and DPA versions
	 */
	public function list(string $osBuild): array {
		return $this->osDpaManager->get($osBuild);
	}

	/**
	 * Returns DPA file name
	 * @param string $osBuild IQRF OS build number
	 * @param Dpa $dpa DPA entity
	 * @return string|null DPA file name
	 */
	public function getFile(string $osBuild, Dpa $dpa): ?string {
		$files = $this->osDpaManager->get($osBuild, $dpa->getVersion());
		if ($files === []) {
			return null;
		}
		$dpaVersion = $files[0]->getDpa();
		$this->filesManager->setUseCredentials($dpaVersion->getAttributes()->isBeta());
		$this->filesManager->setPath($dpaVersion->getDownloadPath());
		foreach ($this->filesManager->list()->getFiles() as $file) {
			foreach ($dpa->getFilePrefixes() as $filePrefix) {
				if (str_starts_with($file->getName(), $filePrefix)) {
					$fileContent = $this->filesManager->download($file->getName());
					$filePath = $file->getName();
					$this->uploadManager->uploadToFs($filePath, $fileContent);
					return $filePath;
				}
			}
		}
		return null;
	}

}
