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

use Grifart\Enum\AutoInstances;
use Grifart\Enum\Enum;

/**
 * IPv4 connection method
 * @method static IPv4Methods AUTO()
 * @method static IPv4Methods DISABLED()
 * @method static IPv4Methods LINK_LOCAL()
 * @method static IPv4Methods MANUAL()
 * @method static IPv4Methods SHARED()
 */
final class IPv4Methods extends Enum {

	use AutoInstances;

	/**
	 * @var string IPv4 configuration should be automatically determined via a method appropriate for the hardware interface, ie DHCP or PPP or some other device-specific manner
	 */
	private const AUTO = 'auto';

	/**
	 * @var string This connection does not use or require IPv4 address and it should be disabled
	 */
	private const DISABLED = 'disabled';

	/**
	 * @var string IPv4 configuration should be automatically configured for link-local-only operation
	 */
	private const LINK_LOCAL = 'link-local';

	/**
	 * @var string All necessary IPv4 configuration is specified in the setting
	 */
	private const MANUAL = 'manual';

	/**
	 * @var string This connection specifies configuration that allows other computers to connect through it to the default network
	 */
	private const SHARED = 'shared';

}
