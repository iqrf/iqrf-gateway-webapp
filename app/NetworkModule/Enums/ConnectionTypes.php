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
 * Network connection type enum
 * @method static ConnectionTypes BRIDGE()
 * @method static ConnectionTypes ETHERNET()
 * @method static ConnectionTypes TUN()
 * @method static ConnectionTypes VLAN()
 * @method static ConnectionTypes VPN()
 * @method static ConnectionTypes WIFI()
 */
final class ConnectionTypes extends Enum {

	use AutoInstances;

	/**
	 * Bridge connection
	 */
	private const BRIDGE = 'bridge';

	/**
	 * Ethernet connection
	 */
	private const ETHERNET = '802-3-ethernet';

	/**
	 * TUN connection
	 */
	private const TUN = 'tun';

	/**
	 * VLAN connection
	 */
	private const VLAN = 'vlan';

	/**
	 * VPN connection
	 */
	private const VPN = 'vpn';

	/**
	 * WiFi connection
	 */
	private const WIFI = '802-11-wireless';

}
