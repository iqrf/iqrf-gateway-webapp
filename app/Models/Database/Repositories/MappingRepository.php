<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

namespace App\Models\Database\Repositories;

use App\Models\Database\Entities\Mapping;
use Doctrine\ORM\EntityRepository;

/**
 * Mapping repository
 * @extends EntityRepository<Mapping>
 */
class MappingRepository extends EntityRepository {

	/**
	 * Finds mapping by specified name
	 * @param string $name Mapping name
	 * @return Mapping|null Mapping entity
	 */
	public function findMappingByName(string $name): ?Mapping {
		return $this->findOneBy(['name' => $name]);
	}

	/**
	 * Lists names of existing mappings
	 * @return array<string> Array of mapping names
	 */
	public function listMappingNamesWithTypes(): array {
		$array = [];
		foreach ($this->findAll() as $mapping) {
			$array[$mapping->getId()] = sprintf('ID:%d - %s (%s)', $mapping->getId(), $mapping->getName(), $mapping->getType()->name);
		}
		return $array;
	}

}
