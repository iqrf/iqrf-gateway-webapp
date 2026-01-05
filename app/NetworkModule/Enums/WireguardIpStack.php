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

namespace App\NetworkModule\Enums;

/**
 * Wireguard IP stack type
 */
enum WireguardIpStack: string {

	/// Dual-stack
	case DUAL = 'both';
	/// IPv4-only
	case IPV4 = 'ipv4';
	/// IPv6-only
	case IPV6 = 'ipv6';

	/**
	 * Serializes stack type into JSON string
	 * @return string JSON serialized data
	 */
	public function jsonSerialize(): string {
		return $this->value;
	}

}
