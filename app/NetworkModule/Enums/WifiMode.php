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

use Grifart\Enum\AutoInstances;
use Grifart\Enum\Enum;
use Grifart\Enum\MissingValueDeclarationException;

/**
 * WiFi modes
 * @method static WifiMode ADHOC()
 * @method static WifiMode AP()
 * @method static WifiMode INFRA()
 * @method static WifiMode MESH()
 */
final class WifiMode extends Enum {

	use AutoInstances;

	/**
	 * @var string Ad-Hoc 802.11 network
	 */
	private const ADHOC = 'adhoc';

	/**
	 * @var string Device in access point mode
	 */
	private const AP = 'ap';

	/**
	 * @var string Device in infrastructure mode
	 */
	private const INFRA = 'infrastructure';

	/**
	 * @var string 802.11s mesh point
	 */
	private const MESH = 'mesh';

	/**
	 * Builds enumeration from its scalar value.
	 * @param string $mode WiFi network mode scalar
	 * @return WifiMode WiFI network mode
	 */
	public static function fromNetworkList(string $mode): WifiMode {
		switch ($mode) {
			case 'Ad-Hoc':
				return self::ADHOC();
			case 'Infra':
				return self::INFRA();
			case 'Mesh':
				return self::MESH();
			default:
				throw new MissingValueDeclarationException('There is no value for enum \'' . self::class . '\' and scalar value \'' . $mode . '\'.');
		}
	}

}
