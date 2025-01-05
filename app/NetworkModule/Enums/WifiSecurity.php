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
 * WiFi Security enum
 * @method static WifiSecurity OPEN()
 * @method static WifiSecurity OWE()
 * @method static WifiSecurity WEP()
 * @method static WifiSecurity WPA_ENTERPRISE()
 * @method static WifiSecurity WPA_PERSONAL()
 * @method static WifiSecurity WPA2_ENTERPRISE()
 * @method static WifiSecurity WPA2_PERSONAL()
 * @method static WifiSecurity WPA3_ENTERPRISE()
 * @method static WifiSecurity WPA3_PERSONAL()
 */
final class WifiSecurity extends Enum {

	use AutoInstances;

	/**
	 * @var string Open WiFi network
	 */
	private const OPEN = 'Open';

	/**
	 * @var string OWE
	 */
	private const OWE = 'OWE';

	/**
	 * @var string WEP
	 */
	private const WEP = 'WEP';

	/**
	 * @var string WPA Enterprise
	 */
	private const WPA_ENTERPRISE = 'WPA-Enterprise';

	/**
	 * @var string WPA Personal
	 */
	private const WPA_PERSONAL = 'WPA-Personal';

	/**
	 * @var string WPA2 Enterprise
	 */
	private const WPA2_ENTERPRISE = 'WPA2-Enterprise';

	/**
	 * @var string WPA2 Personal
	 */
	private const WPA2_PERSONAL = 'WPA2-Personal';

	/**
	 * @var string WPA3 Enterprise
	 */
	private const WPA3_ENTERPRISE = 'WPA3-Enterprise';

	/**
	 * @var string WPA3 Personal
	 */
	private const WPA3_PERSONAL = 'WPA3-Personal';

	/**
	 * Creates WiFi security enum from nmcli
	 * @param string $nmCli nmcli security column
	 * @return WifiSecurity WiFi security enum
	 */
	public static function fromNmCli(string $nmCli): self {
		$array = explode(' ', $nmCli);
		if (in_array('OWE', $array, true)) {
			return self::OWE();
		}
		if (in_array('WEP', $array, true)) {
			return self::WEP();
		}
		if (in_array('WPA3', $array, true)) {
			if (in_array('802.1X', $array, true)) {
				return self::WPA3_ENTERPRISE();
			}
			return self::WPA3_PERSONAL();
		}
		if (in_array('WPA2', $array, true)) {
			if (in_array('802.1X', $array, true)) {
				return self::WPA2_ENTERPRISE();
			}
			return self::WPA2_PERSONAL();
		}
		if (in_array('WPA', $array, true)) {
			if (in_array('802.1X', $array, true)) {
				return self::WPA_ENTERPRISE();
			}
			return self::WPA_PERSONAL();
		}
		return self::OPEN();
	}

}
