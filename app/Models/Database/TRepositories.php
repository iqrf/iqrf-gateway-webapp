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

namespace App\Models\Database;

use App\Models\Database\Entities\ApiKey;
use App\Models\Database\Entities\ControllerPinConfiguration;
use App\Models\Database\Entities\IqrfOsPatch;
use App\Models\Database\Entities\Mapping;
use App\Models\Database\Entities\NetworkOperator;
use App\Models\Database\Entities\PasswordRecovery;
use App\Models\Database\Entities\SshKey;
use App\Models\Database\Entities\User;
use App\Models\Database\Entities\UserInvitation;
use App\Models\Database\Entities\UserVerification;
use App\Models\Database\Entities\WireGuardInterface;
use App\Models\Database\Entities\WireGuardInterfaceIpv4;
use App\Models\Database\Entities\WireGuardInterfaceIpv6;
use App\Models\Database\Entities\WireGuardPeer;
use App\Models\Database\Entities\WireGuardPeerAddress;
use App\Models\Database\Repositories\ApiKeyRepository;
use App\Models\Database\Repositories\ControllerPinConfigurationRepository;
use App\Models\Database\Repositories\IqrfOsPatchRepository;
use App\Models\Database\Repositories\MappingRepository;
use App\Models\Database\Repositories\NetworkOperatorRepository;
use App\Models\Database\Repositories\PasswordRecoveryRepository;
use App\Models\Database\Repositories\SshKeyRepository;
use App\Models\Database\Repositories\UserInvitationRepository;
use App\Models\Database\Repositories\UserRepository;
use App\Models\Database\Repositories\UserVerificationRepository;
use App\Models\Database\Repositories\WireGuardInterfaceIpv4Repository;
use App\Models\Database\Repositories\WireGuardInterfaceIpv6Repository;
use App\Models\Database\Repositories\WireGuardInterfaceRepository;
use App\Models\Database\Repositories\WireGuardPeerAddressRepository;
use App\Models\Database\Repositories\WireGuardPeerRepository;

/**
 * @mixin EntityManager
 */
trait TRepositories {

	/**
	 * Returns the API key repository
	 * @return ApiKeyRepository API key repository
	 */
	public function getApiKeyRepository(): ApiKeyRepository {
		return $this->getRepository(ApiKey::class);
	}

	/**
	 * Returns the controller pin configuration repository
	 * @return ControllerPinConfigurationRepository Controller pin configuration repository
	 */
	public function getControllerPinConfigurationRepository(): ControllerPinConfigurationRepository {
		return $this->getRepository(ControllerPinConfiguration::class);
	}

	/**
	 * Returns the IQRF OS patch repository
	 * @return IqrfOsPatchRepository IQRF OS patch repository
	 */
	public function getIqrfOsPatchRepository(): IqrfOsPatchRepository {
		return $this->getRepository(IqrfOsPatch::class);
	}

	/**
	 * Returns the mapping repository
	 * @return MappingRepository Mapping repository
	 */
	public function getMappingRepository(): MappingRepository {
		return $this->getRepository(Mapping::class);
	}

	/**
	 * Returns the network operator repository
	 * @return NetworkOperatorRepository Network operator repository
	 */
	public function getNetworkOperatorRepository(): NetworkOperatorRepository {
		return $this->getRepository(NetworkOperator::class);
	}

	/**
	 * Returns the password recovery repository
	 * @return PasswordRecoveryRepository Password recovery repository
	 */
	public function getPasswordRecoveryRepository(): PasswordRecoveryRepository {
		return $this->getRepository(PasswordRecovery::class);
	}

	/**
	 * Returns the SSH key repository
	 * @return SshKeyRepository SSH key repository
	 */
	public function getSshKeyRepository(): SshKeyRepository {
		return $this->getRepository(SshKey::class);
	}

	/**
	 * Returns the user invitation repository
	 * @return UserInvitationRepository User invitation repository
	 */
	public function getUserInvitationRepository(): UserInvitationRepository {
		return $this->getRepository(UserInvitation::class);
	}

	/**
	 * Returns the user repository
	 * @return UserRepository User repository
	 */
	public function getUserRepository(): UserRepository {
		return $this->getRepository(User::class);
	}

	/**
	 * Returns the user verification repository
	 * @return UserVerificationRepository User verification repository
	 */
	public function getUserVerificationRepository(): UserVerificationRepository {
		return $this->getRepository(UserVerification::class);
	}

	/**
	 * Returns the WireGuard interface IPv4 repository
	 * @return WireGuardInterfaceIpv4Repository WireGuard interface IPv4 repository
	 */
	public function getWireGuardInterfaceIpv4Repository(): WireGuardInterfaceIpv4Repository {
		return $this->getRepository(WireGuardInterfaceIpv4::class);
	}

	/**
	 * Returns the WireGuard interface IPv6 repository
	 * @return WireGuardInterfaceIpv6Repository WireGuard interface IPv6 repository
	 */
	public function getWireGuardInterfaceIpv6Repository(): WireGuardInterfaceIpv6Repository {
		return $this->getRepository(WireGuardInterfaceIpv6::class);
	}

	/**
	 * Returns the WireGuard interface repository
	 * @return WireGuardInterfaceRepository WireGuard interface repository
	 */
	public function getWireGuardInterfaceRepository(): WireGuardInterfaceRepository {
		return $this->getRepository(WireGuardInterface::class);
	}

	/**
	 * Returns the WireGuard peer repository
	 * @return WireGuardPeerRepository WireGuard peer repository
	 */
	public function getWireGuardPeerRepository(): WireGuardPeerRepository {
		return $this->getRepository(WireGuardPeer::class);
	}

	/**
	 * Returns the WireGuard peer address repository
	 * @return WireGuardPeerAddressRepository WireGuard peer address repository
	 */
	public function getWireGuardPeerAddressRepository(): WireGuardPeerAddressRepository {
		return $this->getRepository(WireGuardPeerAddress::class);
	}

}
