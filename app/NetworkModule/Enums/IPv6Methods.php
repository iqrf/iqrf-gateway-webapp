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
 * IPv6 connection method enum
 */
enum IPv6Methods: string {

	/// IPv6 configuration should be automatically determined via a method appropriate for the hardware interface, ie router advertisements, DHCP, or PPP or some other device-specific manner
	case AUTO = 'auto';
	/// IPv6 is disabled for the configuration
	case DISABLED = 'disabled';
	/// IPv6 configuration should be automatically determined via DHCPv6 only and router advertisements should be ignored
	case DHCP = 'dhcp';
	/// IPv6 is not required or is handled by some other mechanism, and NetworkManager should not configure IPv6 for this connection
	case IGNORE = 'ignore';
	/// IPv6 configuration should be automatically configured for link-local-only operation
	case LINK_LOCAL = 'link-local';
	/// All necessary IPv6 configuration is specified in the setting
	case MANUAL = 'manual';
	/// This connection specifies configuration that allows other computers to connect through it to the default network
	case SHARED = 'shared';

}
