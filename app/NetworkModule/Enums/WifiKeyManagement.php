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
 * WiFi connection key management
 * @method static WifiKeyManagement DYNAMIC_WEP()
 * @method static WifiKeyManagement OWE()
 * @method static WifiKeyManagement SAE()
 * @method static WifiKeyManagement STATIC_WEP()
 * @method static WifiKeyManagement WPA_EAP()
 * @method static WifiKeyManagement WPA_PSK()
 */
final class WifiKeyManagement extends Enum {

	use AutoInstances;

	/**
	 * Dynamic WEP
	 */
	private const DYNAMIC_WEP = 'ieee8021x';

	/**
	 * OWE
	 */
	private const OWE = 'owe';

	/**
	 * SAE
	 */
	private const SAE = 'sae';

	/**
	 * Static WEP
	 */
	private const STATIC_WEP = 'none';

	/**
	 * WPA-EAP
	 */
	private const WPA_EAP = 'wpa-eap';

	/**
	 * WPA-PSK
	 */
	private const WPA_PSK = 'wpa-psk';

}
