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

namespace App\Models\Database\Repositories;

use App\Models\Database\Entities\Mapping;
use Doctrine\ORM\EntityRepository;
use function assert;

/**
 * Mapping repository
 */
class MappingRepository extends EntityRepository {

	/**
	 * Retrieve all mappings
	 * @return array<Mapping> Mappings
	 */
	public function findMappings(): array {
		$array = [];
		foreach ($this->findAll() as $mapping) {
			assert($mapping instanceof Mapping);
			array_push($array, $mapping);
		}
		return $array;
	}

	/**
	 * Lists all mappings as array of names
	 * @return array<string> Mapping names
	 */
	public function listMappings(): array {
		$array = [];
		foreach ($this->findAll() as $mapping) {
			assert($mapping instanceof Mapping);
			array_push($array, $mapping->getName());
		}
		return $array;
	}

}
