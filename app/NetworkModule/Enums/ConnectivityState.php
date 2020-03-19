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
 * Network connectivity state
 * @method static ConnectivityState NONE()
 * @method static ConnectivityState PORTAL()
 * @method static ConnectivityState LIMITED()
 * @method static ConnectivityState FULL()
 * @method static ConnectivityState UNKNOWN()
 */
final class ConnectivityState extends Enum {

	use AutoInstances;

	/**
	 * The host is not connected to any network
	 */
	private const NONE = 'none';

	/**
	 * The host is behind a captive portal and cannot reach the full Internet
	 */
	private const PORTAL = 'portal';

	/**
	 * The host is connected to a network, but it has no access to the Internet
	 */
	private const LIMITED = 'limited';

	/**
	 * The host is connected to a network and has full access to the Internet
	 */
	private const FULL = 'full';

	/**
	 * The connectivity status cannot be found out
	 */
	private const UNKNOWN = 'unknown';

}
