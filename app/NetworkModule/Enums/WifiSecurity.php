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

/**
 * WiFi Security enum
 */
enum WifiSecurity: string {

	/// Open WiFi network
	case OPEN = 'Open';
	/// OWE
	case OWE = 'OWE';
	/// WEP
	case WEP = 'WEP';
	/// WPA Enterprise
	case WPA_ENTERPRISE = 'WPA-Enterprise';
	/// WPA Personal
	case WPA_PERSONAL = 'WPA-Personal';
	/// WPA2 Enterprise
	case WPA2_ENTERPRISE = 'WPA2-Enterprise';
	/// WPA2 Personal
	case WPA2_PERSONAL = 'WPA2-Personal';
	/// WPA3 Enterprise
	case WPA3_ENTERPRISE = 'WPA3-Enterprise';
	/// WPA3 Personal
	case WPA3_PERSONAL = 'WPA3-Personal';

	/**
	 * Creates WiFi security enum from nmcli
	 * @param string $nmCli nmcli security column
	 * @return WifiSecurity WiFi security enum
	 */
	public static function fromNmCli(string $nmCli): self {
		$array = explode(' ', $nmCli);
		if (in_array('OWE', $array, true)) {
			return self::OWE;
		}
		if (in_array('WEP', $array, true)) {
			return self::WEP;
		}
		if (in_array('WPA3', $array, true)) {
			if (in_array('802.1X', $array, true)) {
				return self::WPA3_ENTERPRISE;
			}
			return self::WPA3_PERSONAL;
		}
		if (in_array('WPA2', $array, true)) {
			if (in_array('802.1X', $array, true)) {
				return self::WPA2_ENTERPRISE;
			}
			return self::WPA2_PERSONAL;
		}
		if (in_array('WPA', $array, true)) {
			if (in_array('802.1X', $array, true)) {
				return self::WPA_ENTERPRISE;
			}
			return self::WPA_PERSONAL;
		}
		return self::OPEN;
	}

}
