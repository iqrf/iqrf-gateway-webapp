<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2021 IQRF Tech s.r.o.
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

namespace App\NetworkModule\Entities\WifiSecurity;

use App\NetworkModule\Entities\INetworkManagerEntity;
use App\NetworkModule\Entities\WifiConnectionSecurity;
use App\NetworkModule\Enums\EapPhaseOneMethod;
use App\NetworkModule\Enums\EapPhaseTwoMethod;
use App\NetworkModule\Utils\NmCliConnection;
use stdClass;

/**
 * EAP (Extensible Authentication Protocol) entity
 */
class Eap implements INetworkManagerEntity {

	/**
	 * nmcli 802-1x prefix
	 */
	private const NMCLI_PREFIX = '802-1x';

	/**
	 * @var EapPhaseOneMethod|null EAP phase one authentication method
	 */
	private $phaseOne;

	/**
	 * @var EapPhaseTwoMethod|null EAP phase two authentication method
	 */
	private $phaseTwo;

	/**
	 * @var string EAP anonymous identity string
	 */
	private $anonymousIdentity;

	/**
	 * @var string EAP CA certificate
	 */
	private $cert;

	/**
	 * @var string EAP identity string
	 */
	private $identity;

	/**
	 * @var string EAP password
	 */
	private $password;

	/**
	 * Constructor
	 * @param EapPhaseOneMethod $phaseOne EAP phase one authentication method
	 * @param EapPhaseTwoMethod $phaseTwo EAP phase two authentication method
	 * @param string $anonymousIdentity EAP anonymous identity string
	 * @param string $cert EAP CA certificate
	 * @param string $identity EAP identity string
	 * @param string $password EAP password
	 */
	public function __construct(?EapPhaseOneMethod $phaseOne, ?EapPhaseTwoMethod $phaseTwo, string $anonymousIdentity, string $cert, string $identity, string $password) {
		$this->phaseOne = $phaseOne;
		$this->phaseTwo = $phaseTwo;
		$this->anonymousIdentity = $anonymousIdentity;
		$this->cert = $cert;
		$this->identity = $identity;
		$this->password = $password;
	}

	/**
	 * Deserializes EAP entity from JSON
	 * @param stdClass $json JSON serialized data
	 * @return INetworkManagerEntity EAP entity
	 */
	public static function jsonDeserialize(stdClass $json): INetworkManagerEntity {
		return new self(
			$json->phaseOne,
			$json->phaseTwo,
			$json->anonymousIdentity,
			$json->cert,
			$json->username,
			$json->password
		);
	}

	/**
	 * Serializes EAP entity into JSON
	 * @return array<string, string> JSON serialized data
	 */
	public function jsonSerialize(): array {
		return [
			'phaseOneMethod' => ($this->phaseOne !== null) ? $this->phaseOne->jsonSerialize() : null,
			'phaseTwoMethod' => ($this->phaseTwo !== null) ? $this->phaseTwo->jsonSerialize() : null,
			'anonymousIdentity' => $this->anonymousIdentity,
			'cert' => $this->cert,
			'identity' => $this->identity,
			'password' => $this->password,
		];
	}

	/**
	 * Deserializes EAP entity from nmcli configuration
	 * @param string $nmCli nmcli configuration
	 * @return INetworkManagerEntity EAP entity
	 */
	public static function nmCliDeserialize(string $nmCli): INetworkManagerEntity {
		$array = NmCliConnection::decode($nmCli, self::NMCLI_PREFIX);
		if (count($array) === 0) {
			return new self(null, null, '', '', '', '');
		}
		return new self(
			EapPhaseOneMethod::fromScalar($array['eap']),
			EapPhaseTwoMethod::fromScalar($array['phase2-auth']),
			$array['anonymous-identity'],
			$array['ca-cert'],
			$array['identity'],
			$array['password']
		);
	}

	/**
	 * Serializes EAP entity into nmcli configuration string
	 * @return string nmcli configuration
	 */
	public function nmCliSerialize(): string {
		$array = [
			'eap' => (string) $this->phaseOne->toScalar(),
			'phase2-auth' => (string) $this->phaseTwo->toScalar(),
			'anonymous-identity' => $this->anonymousIdentity,
			'ca-cert' => $this->cert,
			'identity' => $this->identity,
			'password' => $this->password,
		];
		return NmCliConnection::encode($array, WifiConnectionSecurity::NMCLI_PREFIX);
	}

}
