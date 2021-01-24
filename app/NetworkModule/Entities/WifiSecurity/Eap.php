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
use App\NetworkModule\Enums\EapPhaseOneMethod;
use App\NetworkModule\Enums\EapPhaseTwoMethod;

/**
 * EAP (Extensible Authentication Protocol) entity
 */
class Eap implements INetworkManagerEntity {

	/**
	 * @var EapPhaseOneMethod EAP phase one authentication method
	 */
	private $phaseOne;

	/**
	 * @var EapPhaseTwoMethod EAP phase two authentication method
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
	 * @param string $caCert EAP CA certificate
	 * @param string $identity EAP identity string
	 * @param string $password EAP password
	 */
	public function __construct(EapPhaseOneMethod $phaseOne, EapPhaseTwoMethod $phaseTwo, string $anonymousIdentity, string $cert, string $username, string $password) {
		$this->phaseOne = $phaseOne;
		$this->phaseTwo = $phaseTwo;
		$this->anonymousIdentity = $anonymousIdentity;
		$this->cert = $cert;
		$this->identity = $identity;
		$this->password = $password;
	}

	/**
	 * Serializes EAP entity into JSON
	 * @return array<string, string> JSON serialized data
	 */
	public function jsonSerialize(): array {
		return [
			'phaseOneMethod' => $this->phaseOne,
			'phaseTwoMethod' => $this->phaseTwo,
			'anonymousIdentity' => $this->anonymousIdentity,
			'cert' => $this->cert,
			'identity' => $this->identity,
			'password' => $this->password,
		];
	}

}
