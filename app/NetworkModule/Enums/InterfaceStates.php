<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

use Nette\Utils\Strings;

/**
 * Network interface states enum
 */
enum InterfaceStates: string {

	/// The interface has a network connection
	case CONNECTED = 'connected';
	/// The interface is connecting to the network
	case CONNECTING = 'connecting';
	/// The interface is configuring connection to the requested network
	case CONFIG = 'connecting (configuring)';
	/// The interface is disconnecting from the current network and the interface is cleaning up resources used for that connection
	case DEACTIVATING = 'deactivating';
	/// The interface can be activated, but is currently idle and not connected to the network
	case DISCONNECTED = 'disconnected';
	/// The interface failed to connect to the requested network
	case FAILED = 'connection failed';
	/// The interface is getting IP configuration
	case IP_CONFIG = 'connecting (getting IP configuration)';
	/// The interface is checking whether further action is required for the requested network connection
	case IP_CHECK = 'connecting (checking IP connectivity)';
	/// The interface requires more information to continue connecting to the requested network
	case NEED_AUTH = 'connecting (need authentication)';
	/// The interface is preparing the connection to the network
	case PREPARE = 'connecting (prepare)';
	/// The interface is waiting for a secondary connection (like a VPN) which must activated before the interface can be activated
	case SECONDARIES = 'connecting (starting secondary connections)';
	/// The interface is managed by NetworkManager, but is not available for use
	case UNAVAILABLE = 'unavailable';
	/// The interface is recognized, but not managed by NetworkManager
	case UNMANAGED = 'unmanaged';
	/// The interface's state is unknown
	case UNKNOWN = 'unknown';

	/**
	 * Creates a new network interface state enum from nmcli
	 * @param string $nmCli nmcli network interface state string
	 * @return static Network interface state
	 */
	public static function fromNmCli(string $nmCli): self {
		$state = Strings::match($nmCli, '~^\d+ \((?\'state\'.+)\)$~')['state'];
		$state = Strings::replace($state, '# \(externally\)#', '');
		return self::from($state);
	}

}
