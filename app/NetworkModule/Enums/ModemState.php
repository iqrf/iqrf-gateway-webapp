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
 * GSM modem modes
 * @method static ModemState FAILED()
 * @method static ModemState UNKNOWN()
 * @method static ModemState INITIALIZING()
 * @method static ModemState LOCKED()
 * @method static ModemState DISABLED()
 * @method static ModemState DISABLING()
 * @method static ModemState ENABLING()
 * @method static ModemState ENABLED()
 * @method static ModemState SEARCHING()
 * @method static ModemState REGISTERED()
 * @method static ModemState DISCONNECTING()
 * @method static ModemState CONNECTING()
 * @method static ModemState CONNECTED()
 */
final class ModemState extends Enum {

	use AutoInstances;

	/**
	 * @var string The modem is unusable
	 */
	private const FAILED = 'failed';

	/**
	 * @var string State unknown or not reportable
	 */
	private const UNKNOWN = 'unknown';

	/**
	 * @var string The modem is currently being initialized
	 */
	private const INITIALIZING = 'initializing';

	/**
	 * @var string The modem needs to be unlocked
	 */
	private const LOCKED = 'locked';

	/**
	 * @var string The modem is not enabled and is powered down
	 */
	private const DISABLED = 'disabled';

	/**
	 * @var string The modem is currently transitioning to the DISABLED state
	 */
	private const DISABLING = 'disabling';

	/**
	 * @var string The modem is currently transitioning to the ENABLED state
	 */
	private const ENABLING = 'enabling';

	/**
	 * @var string The modem is enabled and powered on but not registered with a network provider and not available for data connections
	 */
	private const ENABLED = 'enabled';

	/**
	 * @var string The modem is searching for a network provider to register with
	 */
	private const SEARCHING = 'searching';

	/**
	 * @var string The modem is registered with a network provider, and data connections and messaging may be available for use
	 */
	private const REGISTERED = 'registered';

	/**
	 * @var string The modem is disconnecting and deactivating the last active packet data bearer. This state will not be entered if more than one packet data bearer is active and one of the active bearers is deactivated.
	 */
	private const DISCONNECTING = 'disconnecting';

	/**
	 * @var string The modem is activating and connecting the first packet data bearer. Subsequent bearer activations when another bearer is already active do not cause this state to be entered
	 */
	private const CONNECTING = 'connecting';

	/**
	 * @var string One or more packet data bearers is active and connected
	 */
	private const CONNECTED = 'connected';

}
