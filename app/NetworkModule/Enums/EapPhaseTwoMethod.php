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
use JsonSerializable;

/**
 * EAP (Extensible Authentication Protocol) phase two authentication method enum
 * @method static EapPhaseTwoMethod GTC()
 * @method static EapPhaseTwoMethod MD5()
 * @method static EapPhaseTwoMethod MSCHAPV2()
 */
final class EapPhaseTwoMethod extends Enum implements JsonSerializable {

	use AutoInstances;

	/**
	 * @var string GTC method
	 */
	private const GTC = 'gtc';

	/**
	 * @var string MD5 method
	 */
	private const MD5 = 'md5';

	/**
	 * @var string MSCHAPv2 method
	 */
	private const MSCHAPV2 = 'mschapv2';

	/**
	 * Serializes EAP phase two authentication method into JSON string
	 * @return string JSON serialized data
	 */
	public function jsonSerialize(): string {
		return (string) $this->toScalar();
	}

}
