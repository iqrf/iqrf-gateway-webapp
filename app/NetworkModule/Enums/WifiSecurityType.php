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

use App\NetworkModule\Entities\WifiConnectionSecurity;
use App\NetworkModule\Utils\NmCliConnection;
use Grifart\Enum\AutoInstances;
use Grifart\Enum\Enum;
use Grifart\Enum\MissingValueDeclarationException;

/**
 * WiFi security type enum
 * @method static WifiSecurityType OPEN()
 * @method static WifiSecurityType LEAP()
 * @method static WifiSecurityType WEP()
 * @method static WifiSecurityType WPA_EAP()
 * @method static WifiSecurityType WPA_PSK()
 */
final class WifiSecurityType extends Enum {

	use AutoInstances;

	/**
	 * @var string Open
	 */
	private const OPEN = 'open';

	/**
	 * @var string WEP
	 */
	private const WEP = 'wep';

	/**
	 * @var string Cisco LEAP
	 */
	private const LEAP = 'leap';

	/**
	 * @var string WPA-EAP
	 */
	private const WPA_EAP = 'wpa-eap';

	/**
	 * @var string WPA-PSK
	 */
	private const WPA_PSK = 'wpa-psk';

	/**
	 * Deserializes WiFi security entity from nmcli connection configuration
	 * @param array<string, array<string, array<string>|string>> $nmCli nmcli connection configuration
	 * @return self WiFi security type entity
	 */
	public static function nmCliDeserialize(array $nmCli): self {
		$conf = $nmCli[WifiConnectionSecurity::NMCLI_PREFIX] ?? [];
		if ($conf === []) {
			return self::OPEN();
		}
		$authAlg = WifiAuthAlgorithm::fromScalar($conf['auth-alg'] ?? '');
		$keyManagement = WifiKeyManagement::fromScalar($conf['key-mgmt']);
		switch ($keyManagement) {
			case WifiKeyManagement::DYNAMIC_WEP():
				if ($authAlg === WifiAuthAlgorithm::LEAP()) {
					return self::LEAP();
				}
				return self::WEP();
			case WifiKeyManagement::STATIC_WEP():
				return self::WEP();
			case WifiKeyManagement::WPA_EAP():
				return self::WPA_EAP();
			case WifiKeyManagement::WPA_PSK():
				return self::WPA_PSK();
			default:
				throw new MissingValueDeclarationException('There is no value for enum \'' . self::class . '\' and scalar value \'' . $keyManagement->toScalar() . '\'.');
		}
	}

	/**
	 * Serializes WiFi security type into nmcli configuration string
	 * @return string nmcli configuration
	 */
	public function nmCliSerialize(): string {
		switch ($this) {
			case self::LEAP():
				$authAlgorithm = WifiAuthAlgorithm::LEAP();
				$keyManagement = WifiKeyManagement::DYNAMIC_WEP();
				break;
			case self::WEP():
				$authAlgorithm = WifiAuthAlgorithm::OPEN_SYSTEM();
				$keyManagement = WifiKeyManagement::STATIC_WEP();
				break;
			case self::WPA_EAP():
				$keyManagement = WifiKeyManagement::WPA_EAP();
				break;
			case self::WPA_PSK():
				$keyManagement = WifiKeyManagement::WPA_PSK();
				break;
			case self::OPEN():
			default:
				return '';
		}
		$config = ['key-mgmt' => $keyManagement->toScalar()];
		if (isset($authAlgorithm)) {
			$config['auth-alg'] = $authAlgorithm->toScalar();
		}
		return NmCliConnection::encode($config, WifiConnectionSecurity::NMCLI_PREFIX);
	}

}
