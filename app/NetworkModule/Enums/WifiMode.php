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

use ValueError;

/**
 * WiFi modes
 */
enum WifiMode: string {

	/// Ad-Hoc 802.11 network
	case ADHOC = 'adhoc';
	/// Device in access point mode
	case AP = 'ap';
	/// Device in infrastructure mode
	case INFRA = 'infrastructure';
	/// 802.11s mesh point
	case MESH = 'mesh';

	/**
	 * Builds enumeration from its scalar value.
	 * @param string $mode WiFi network mode scalar
	 * @return WifiMode WiFI network mode
	 */
	public static function fromNetworkList(string $mode): WifiMode {
		return match ($mode) {
			'Ad-Hoc' => self::ADHOC,
			'Infra' => self::INFRA,
			'Mesh' => self::MESH,
			default => throw new ValueError('There is no value for enum \'' . self::class . '\' and scalar value \'' . $mode . '\'.'),
		};
	}

}
