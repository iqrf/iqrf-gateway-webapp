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
use Nette\Utils\Strings;

/**
 * Network interface states enum
 * @method static InterfaceStates CONNECTED()
 * @method static InterfaceStates CONNECTING()
 * @method static InterfaceStates CONFIG()
 * @method static InterfaceStates DEACTIVATING()
 * @method static InterfaceStates DISCONNECTED()
 * @method static InterfaceStates FAILED()
 * @method static InterfaceStates IP_CONFIG()
 * @method static InterfaceStates IP_CHECK()
 * @method static InterfaceStates NEED_AUTH()
 * @method static InterfaceStates PREPARE()
 * @method static InterfaceStates SECONDARIES()
 * @method static InterfaceStates UNAVAILABLE()
 * @method static InterfaceStates UNMANAGED()
 * @method static InterfaceStates UNKNOWN()
 */
final class InterfaceStates extends Enum {

	use AutoInstances;

	/**
	 * The interface has a network connection
	 */
	private const CONNECTED = 'connected';

	/**
	 * The interface is connecting to the network
	 */
	private const CONNECTING = 'connecting';

	/**
	 * The interface is configuring connection to the requested network
	 */
	private const CONFIG = 'connecting (configuring)';

	/**
	 * The interface is disconnecting from the current network and the interface is cleaning up resources used for that connection
	 */
	private const DEACTIVATING = 'deactivating';
	/**
	 * The interface can be activated, but is currently idle and not connected to the network
	 */
	private const DISCONNECTED = 'disconnected';

	/**
	 * The interface failed to connect to the requested network
	 */
	private const FAILED = 'connection failed';

	/**
	 * The interface is getting IP configuration
	 */
	private const IP_CONFIG = 'connecting (getting IP configuration)';

	/**
	 * The interface is checking whether further action is required for the requested network connection
	 */
	private const IP_CHECK = 'connecting (checking IP connectivity)';

	/**
	 * The interface requires more information to continue connecting to the requested network
	 */
	private const NEED_AUTH = 'connecting (need authentication)';

	/**
	 * The interface is preparing the connection to the network
	 */
	private const PREPARE = 'connecting (prepare)';

	/**
	 * The interface is waiting for a secondary connection (like a VPN) which must activated before the interface can be activated
	 */
	private const SECONDARIES = 'connecting (starting secondary connections)';

	/**
	 * The interface is managed by NetworkManager, but is not available for use
	 */
	private const UNAVAILABLE = 'unavailable';

	/**
	 * The interface is recognized, but not managed by NetworkManager
	 */
	private const UNMANAGED = 'unmanaged';

	/**
	 * The interface's state is unknown
	 */
	private const UNKNOWN = 'unknown';

	/**
	 * Creates a new network interface state enum from nmcli
	 * @param string $nmCli nmcli network interface state string
	 * @return static Network interface state
	 */
	public static function fromNmCli(string $nmCli): self {
		$state = Strings::replace($nmCli, '/ \(externally\)/', '');
		return self::fromScalar($state);
	}

}
