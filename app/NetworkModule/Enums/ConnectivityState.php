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

use Nette\Utils\Strings;

/**
 * Network connectivity state
 */
enum ConnectivityState: string {

	/// The host is not connected to any network
	case NONE = 'none';
	/// The host is behind a captive portal and cannot reach the full Internet
	case PORTAL = 'portal';
	/// The host is connected to a network, but it has no access to the Internet
	case LIMITED = 'limited';
	/// The host is connected to a network and has full access to the Internet
	case FULL = 'full';
	/// The connectivity status cannot be found out
	case UNKNOWN = 'unknown';

	/**
	 * Creates a new network connectivity state enum from nmcli
	 * @param string $nmCli nmcli network connectivity state string
	 * @return static Network connectivity state
	 */
	public static function fromNmCli(string $nmCli): self {
		$matches = Strings::match($nmCli, '~^\d+ \((?\'state\'.+)\)$~');
		if ($matches === null || !isset($matches['state'])) {
			return self::UNKNOWN;
		}
		$state = Strings::replace($matches['state'], '# \(externally\)#');
		return self::tryFrom($state) ?? self::UNKNOWN;
	}

}
