<?php

/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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
use JsonSerializable;

/**
 * WEP (Wired Equivalent Privacy) key type enum
 * @method static WepKeyType KEY()
 * @method static WepKeyType PASSPHRASE()
 * @method static WepKeyType UNKNOWN()
 */
final class WepKeyType extends Enum implements JsonSerializable {

	use AutoInstances;

	/**
	 * WEP key
	 */
	private const KEY = 'key';

	/**
	 * WEP passphrase
	 */
	private const PASSPHRASE = 'passphrase';

	/**
	 * Unknown type
	 */
	private const UNKNOWN = 'unknown';

	/**
	 * Serializes WEP key type into JSON string
	 * @return string JSON serialized data
	 */
	public function jsonSerialize(): string {
		return (string) $this->toScalar();
	}

}
