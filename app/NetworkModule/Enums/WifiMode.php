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
 * WiFi modes
 * @method static WifiMode ADHOC()
 * @method static WifiMode INFRA()
 * @method static WifiMode MESH()
 */
final class WifiMode extends Enum {

	use AutoInstances;

	/**
	 * Ad-Hoc 802.11 network
	 */
	private const ADHOC = 'Ad-Hoc';

	/**
	 * Device in infrastructure mode
	 */
	private const INFRA = 'Infra';

	/**
	 * 802.11s mesh point
	 */
	private const MESH = 'Mesh';

}
