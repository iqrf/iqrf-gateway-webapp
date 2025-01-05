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
 * WiFi authentication algorithm enum
 * @method static WifiAuthAlgorithm NONE()
 * @method static WifiAuthAlgorithm OPEN_SYSTEM()
 * @method static WifiAuthAlgorithm SHARED_KEY()
 * @method static WifiAuthAlgorithm LEAP()
 */
final class WifiAuthAlgorithm extends Enum {

	use AutoInstances;

	/**
	 * @var string None
	 */
	private const NONE = '';

	/**
	 * @var string Open system
	 */
	private const OPEN_SYSTEM = 'open';

	/**
	 * @var string Shared key
	 */
	private const SHARED_KEY = 'shared';

	/**
	 * @var string Cisco LEAP
	 */
	private const LEAP = 'leap';

}
