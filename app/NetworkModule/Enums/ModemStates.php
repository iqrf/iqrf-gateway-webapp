<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
 * Modem states
 * @method static ModemStates FAILED()
 * @method static ModemStates UNKNOWN()
 * @method static ModemStates INITIALIZING()
 * @method static ModemStates LOCKED()
 * @method static ModemStates DISABLED()
 * @method static ModemStates DISABLING()
 * @method static ModemStates ENABLING()
 * @method static ModemStates ENABLED()
 * @method static ModemStates SEARCHING()
 * @method static ModemStates REGISTERED()
 * @method static ModemStates DISCONNECTING()
 * @method static ModemStates CONNECTING()
 * @method static ModemStates CONNECTED()
 */
final class ModemStates extends Enum {

	use AutoInstances;

	/**
	 * Modem is not usable
	 */
	private const FAILED = 'failed';

	/**
	 * Unknown or not reportable
	 */
	private const UNKNOWN = 'unknown';

	/**
	 * Currently being initialized
	 */
	private const INITIALIZING = 'initializing';

	/**
	 * Needs to be unlocked
	 */
	private const LOCKED = 'locked';

	/**
	 * Not enabled and powered down
	 */
	private const DISABLED = 'disabled';

	/**
	 * Switching to disabled state
	 */
	private const DISABLING = 'disabling';

	/**
	 * Switching to enabled state
	 */
	private const ENABLING = 'enabling';

	/**
	 * Enabled and powered, but not registered with a network operator
	 */
	private const ENABLED = 'enabled';

	/**
	 * Searching for a network operator to register with
	 */
	private const SEARCHING = 'searching';

	/**
	 * Registered with a network provider, ready for use
	 */
	private const REGISTERED = 'registered';

	/**
	 * Disconnecting and deactivatign packet data bearer
	 */
	private const DISCONNECTING = 'disconnecting';

	/**
	 * Connecting to packet data bearer
	 */
	private const CONNECTING = 'connecting';

	/**
	 * Packet data bearer active and connected
	 */
	private const CONNECTED = 'connected';

	/**
	 * Serializes modem state into JSON
	 */
	public function jsonSerialize(): string {
		return (string) $this->toScalar();
	}

}
