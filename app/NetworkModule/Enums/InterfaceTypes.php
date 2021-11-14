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
 * Interface type enum
 * @method static InterfaceTypes BOND()
 * @method static InterfaceTypes BRIDGE()
 * @method static InterfaceTypes DUMMY()
 * @method static InterfaceTypes ETHERNET()
 * @method static InterfaceTypes LOOPBACK()
 * @method static InterfaceTypes TUN()
 * @method static InterfaceTypes VLAN()
 * @method static InterfaceTypes WIFI()
 * @method static InterfaceTypes WIFI_P2P()
 * @method static InterfaceTypes WIREGUARD()
 */
final class InterfaceTypes extends Enum {

	use AutoInstances;

	/**
	 * Bond master interface
	 */
	private const BOND = 'bond';

	/**
	 * Bridge master interface
	 */
	private const BRIDGE = 'bridge';

	/**
	 * Dummy interface
	 */
	private const DUMMY = 'dummy';

	/**
	 * Wired Ethernet interface
	 */
	private const ETHERNET = 'ethernet';

	/**
	 * Loopback interface
	 */
	private const LOOPBACK = 'loopback';

	/**
	 * TUN or TAP interface
	 */
	private const TUN = 'tun';

	/**
	 * 802.1Q VLAN interface
	 */
	private const VLAN = 'vlan';

	/**
	 * 802.11 WiFi interface
	 */
	private const WIFI = 'wifi';

	/**
	 * WiFi P2P interface
	 */
	private const WIFI_P2P = 'wifi-p2p';

	/**
	 * WireGuard
	 */
	private const WIREGUARD = 'wireguard';

}
