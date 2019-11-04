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

namespace App\IqrfNetModule\Enums;

/**
 * Device types enumeration
 */
class DeviceTypes {

	/**
	 * No device
	 */
	public const NONE = 0;

	/**
	 * Coordinator
	 */
	public const COORDINATOR = 1;

	/**
	 * Bonded device
	 */
	public const BONDED = 2;

	/**
	 * Discovered device
	 */
	public const DISCOVERED = 3;

	/**
	 * Online device
	 */
	public const ONLINE = 4;

	/**
	 * Bonded online device
	 */
	public const BONDED_ONLINE = 5;

	/**
	 * Discovered online device
	 */
	public const DISCOVERED_ONLINE = 6;

}
