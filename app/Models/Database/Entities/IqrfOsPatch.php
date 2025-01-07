<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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
	public function __construct(
		#[ORM\Column(type: Types::STRING, length: 15)]
		public readonly string $moduleType,
		#[ORM\Column(type: Types::INTEGER)]
		public readonly int $fromVersion,
		#[ORM\Column(type: Types::INTEGER)]
		public readonly int $fromBuild,
		#[ORM\Column(type: Types::INTEGER)]
		public readonly int $toVersion,
		#[ORM\Column(type: Types::INTEGER)]
		public readonly int $toBuild,
		#[ORM\Column(type: Types::INTEGER)]
		public readonly int $part,
		#[ORM\Column(type: Types::INTEGER)]
		public readonly int $parts,
		#[ORM\Column(type: Types::STRING, length: 255, unique: true)]
		public readonly string $fileName,
	) {
	}

	/**
	 * Returns JSON serialized IQRF OS patch metadata
	 * @return array{
	 *     id: int|null,
	 *     moduleType: string,
	 *     fromOsVersion: int,
	 *     fromOsBuild: int,
	 *     toOsVersion: int,
	 *     toOsBuild: int,
	 *     partNumber: int,
	 *     parts: int,
	 *     fileName: string,
	 * } JSON serialized IQRF OS patch data
	 */
	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'moduleType' => $this->moduleType,
			'fromOsVersion' => $this->fromVersion,
			'fromOsBuild' => $this->fromBuild,
			'toOsVersion' => $this->toVersion,
			'toOsBuild' => $this->toBuild,
			'partNumber' => $this->part,
			'parts' => $this->parts,
			'fileName' => $this->fileName,
		];
	}

}
