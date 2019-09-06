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

namespace App\NetworkModule\Enums;

use Grifart\Enum\AutoInstances;
use Grifart\Enum\Enum;

/**
 * Network interface states enum
 * @method static InterfaceStates CONNECTED()
 * @method static InterfaceStates DISCONNECTED()
 * @method static InterfaceStates GETTING_IP_CONFIGURATION()
 * @method static InterfaceStates UNAVAILABLE()
 * @method static InterfaceStates UNMANAGED()
 */
final class InterfaceStates extends Enum {

	use AutoInstances;

	/**
	 * The interface has a network connection
	 */
	private const CONNECTED = 'connected';

	/**
	 * The interface can be activated, but is currently idle and not connected to the network
	 */
	private const DISCONNECTED = 'disconnected';


	private const GETTING_IP_CONFIGURATION = 'connecting (getting IP configuration)';

	/**
	 * The interface is managed by NetworkManager, but is not available for use
	 */
	private const UNAVAILABLE = 'unavailable';

	/**
	 * The interface is recognized, but not managed by NetworkManager
	 */
	private const UNMANAGED = 'unmanaged';

}
