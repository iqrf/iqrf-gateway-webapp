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

namespace App\Models\Database\Repositories;

use App\Models\Database\Entities\ApiKey;
use Doctrine\ORM\EntityRepository;

/**
 * API key repository
 * @extends EntityRepository<ApiKey>
 */
class ApiKeyRepository extends EntityRepository {

	/**
	 * Finds one API key by its hash salt value
	 * @param string $salt API key hash salt
	 * @return ApiKey|null API key entity
	 */
	public function findOneBySalt(string $salt): ?ApiKey {
		return $this->findOneBy(['salt' => $salt]);
	}

	/**
	 * Lists API keys as qrray of strings with description and expiration
	 * @return array<int, string> API keys
	 */
	public function listWithDescription(): array {
		$array = [];
		foreach ($this->findAll() as $apiKey) {
			$expiration = $apiKey->getExpiration() === null ? 'none' : $apiKey->getExpiration()->format('c');
			$array[$apiKey->getId()] = sprintf('ID: %d, description: %s, expiration: %s', $apiKey->getId(), $apiKey->getDescription(), $expiration);
		}
		return $array;
	}

}
