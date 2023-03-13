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

namespace App\Models\Database\Entities;

use App\Models\Database\Attributes\TId;
use App\Models\Database\Repositories\IqrfOsPatchRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * IQRF OS patch entity
 */
#[ORM\Entity(repositoryClass: IqrfOsPatchRepository::class)]
#[ORM\Table(name: 'iqrf_os_patches')]
#[ORM\HasLifecycleCallbacks]
class IqrfOsPatch {

	use TId;

	/**
	 * @var string IQRF TR module type
	 */
	#[ORM\Column(type: Types::STRING, length: 15)]
	private string $moduleType;

	/**
	 * @var int Current IQRF OS version
	 */
	#[ORM\Column(type: Types::INTEGER)]
	private int $fromVersion;

	/**
	 * @var int Current IQRF OS build
	 */
	#[ORM\Column(type: Types::INTEGER)]
	private int $fromBuild;

	/**
	 * @var int Next IQRF OS version
	 */
	#[ORM\Column(type: Types::INTEGER)]
	private int $toVersion;

	/**
	 * @var int Next IQRF OS build
	 */
	#[ORM\Column(type: Types::INTEGER)]
	private int $toBuild;

	/**
	 * @var int Part number
	 */
	#[ORM\Column(type: Types::INTEGER)]
	private int $part;

	/**
	 * @var int Total parts
	 */
	#[ORM\Column(type: Types::INTEGER)]
	private int $parts;

	/**
	 * @var string File name
	 */
	#[ORM\Column(type: Types::STRING, length: 255, unique: true)]
	private string $fileName;

	/**
	 * Constructor
	 * @param string $moduleType IQRF TR module type
	 * @param int $fromVersion Current IQRF OS version
	 * @param int $fromBuild Current IQRF OS build
	 * @param int $toVersion Next IQRF OS version
	 * @param int $toBuild Next IQRF OS build
	 * @param int $part Part number
	 * @param int $parts Total parts
	 * @param string $fileName File name
	 */
	public function __construct(string $moduleType, int $fromVersion, int $fromBuild, int $toVersion, int $toBuild, int $part, int $parts, string $fileName) {
		$this->moduleType = $moduleType;
		$this->fromVersion = $fromVersion;
		$this->fromBuild = $fromBuild;
		$this->toVersion = $toVersion;
		$this->toBuild = $toBuild;
		$this->part = $part;
		$this->parts = $parts;
		$this->fileName = $fileName;
	}

	/**
	 * Returns IQRF TR module type
	 * @return string IQRF TR module type
	 */
	public function getModuleType(): string {
		return $this->moduleType;
	}

	/**
	 * Returns current IQRF OS version
	 * @return int Current IQRF OS version
	 */
	public function getFromVersion(): int {
		return $this->fromVersion;
	}

	/**
	 * Returns current IQRF OS build
	 * @return int Current IQRF OS build
	 */
	public function getFromBuild(): int {
		return $this->fromBuild;
	}

	/**
	 * Returns next IQRF OS version
	 * @return int Next IQRF OS version
	 */
	public function getToVersion(): int {
		return $this->toVersion;
	}

	/**
	 * Returns next IQRF OS build
	 * @return int Next IQRF OS build
	 */
	public function getToBuild(): int {
		return $this->toBuild;
	}

	/**
	 * Returns number od IQRF OS patch part
	 * @return int Part number
	 */
	public function getPart(): int {
		return $this->part;
	}

	/**
	 * Returns number of total IQRF OS patch parts
	 * @return int Total parts
	 */
	public function getParts(): int {
		return $this->parts;
	}

	/**
	 * Returns IQRF OS patch file name
	 * @return string File name
	 */
	public function getFileName(): string {
		return $this->fileName;
	}

	/**
	 * Returns JSON serialized IQRF OS patch metadata
	 * @return array{id: int|null, moduleType: string, fromOsVersion: int, fromOsBuild: int, toOsVersion: int, toOsBuild: int, partNumber: int, parts: int, fileName: string} JSON serialized IQRF OS patch data
	 */
	public function jsonSerialize(): array {
		return [
			'id' => $this->getId(),
			'moduleType' => $this->getModuleType(),
			'fromOsVersion' => $this->getFromVersion(),
			'fromOsBuild' => $this->getFromBuild(),
			'toOsVersion' => $this->getToVersion(),
			'toOsBuild' => $this->getToBuild(),
			'partNumber' => $this->getPart(),
			'parts' => $this->getParts(),
			'fileName' => $this->getFileName(),
		];
	}

}
