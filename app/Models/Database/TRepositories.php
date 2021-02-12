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

namespace App\Models\Database;

use App\Models\Database\Entities\ApiKey;
use App\Models\Database\Entities\IqrfOsPatch;
use App\Models\Database\Entities\Mapping;
use App\Models\Database\Entities\User;
use App\Models\Database\Entities\WireguardInterface;
use App\Models\Database\Entities\WireguardPeer;
use App\Models\Database\Entities\WireguardPeerAddress;
use App\Models\Database\Repositories\ApiKeyRepository;
use App\Models\Database\Repositories\IqrfOsPatchRepository;
use App\Models\Database\Repositories\MappingRepository;
use App\Models\Database\Repositories\UserRepository;
use App\Models\Database\Repositories\WireguardInterfaceRepository;
use App\Models\Database\Repositories\WireguardPeerAddressRepository;
use App\Models\Database\Repositories\WireguardPeerRepository;

/**
 * @mixin EntityManager
 */
trait TRepositories {

	/**
	 * Returns the API key repository
	 * @return ApiKeyRepository API key repository
	 */
	public function getApiKeyRepository(): ApiKeyRepository {
		$repository = $this->getRepository(ApiKey::class);
		assert($repository instanceof ApiKeyRepository);
		return $repository;
	}

	/**
	 * Returns the IQRF OS pach repository
	 * @return IqrfOsPatchRepository IQRF OS patch repository
	 */
	public function getIqrfOsPatchRepository(): IqrfOsPatchRepository {
		$repository = $this->getRepository(IqrfOsPatch::class);
		assert($repository instanceof IqrfOsPatchRepository);
		return $repository;
	}

	/**
	 * Returns the mapping repository
	 * @return MappingRepository Mapping repository
	 */
	public function getMappingRepository(): MappingRepository {
		$repository = $this->getRepository(Mapping::class);
		assert($repository instanceof MappingRepository);
		return $repository;
	}

	/**
	 * Returns the user repository
	 * @return UserRepository User repository
	 */
	public function getUserRepository(): UserRepository {
		$repository = $this->getRepository(User::class);
		assert($repository instanceof UserRepository);
		return $repository;
	}

	/**
	 * Returns the wireguard interface repository
	 * @return WireguardInterfaceRepository Wireguard interface repository
	 */
	public function getWireguardInterfaceRepository(): WireguardInterfaceRepository {
		$repository = $this->getRepository(WireguardInterface::class);
		assert($repository instanceof WireguardInterfaceRepository);
		return $repository;
	}

	/**
	 * Returns the wireguard peer repository
	 * @return WireguardPeerRepository Wireguard peer repository
	 */
	public function getWireguardPeerRepository(): WireguardPeerRepository {
		$repository = $this->getRepository(WireguardPeer::class);
		assert($repository instanceof WireguardPeerRepository);
		return $repository;
	}

	/**
	 * Returns the wireguard peer repository
	 * @return WireguardPeerAddressRepository Wireguard peer repository
	 */
	public function getWireguardPeerAddressRepository(): WireguardPeerAddressRepository {
		$repository = $this->getRepository(WireguardPeerAddress::class);
		assert($repository instanceof WireguardPeerAddressRepository);
		return $repository;
	}

}
