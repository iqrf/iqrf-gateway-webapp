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
use JsonSerializable;

/**
 * EAP (Extensible Authentication Protocol) phase one authentication method enum
 * @method static EapPhaseOneMethod FAST()
 * @method static EapPhaseOneMethod LEAP()
 * @method static EapPhaseOneMethod MD5()
 * @method static EapPhaseOneMethod PEAP()
 * @method static EapPhaseOneMethod PWD()
 * @method static EapPhaseOneMethod TLS()
 * @method static EapPhaseOneMethod TTLS()
 */
final class EapPhaseOneMethod extends Enum implements JsonSerializable {

	use AutoInstances;

	/**
	 * @var string FAST method
	 */
	private const FAST = 'fast';

	/**
	 * @var string LEAP method
	 */
	private const LEAP = 'leap';

	/**
	 * @var string MD5 method
	 */
	private const MD5 = 'md5';

	/**
	 * @var string PEAP method
	 */
	private const PEAP = 'peap';

	/**
	 * @var string PWD method
	 */
	private const PWD = 'pwd';

	/**
	 * @var string TLS method
	 */
	private const TLS = 'tls';

	/**
	 * @var string TTLS method
	 */
	private const TTLS = 'ttls';

	/**
	 * Serializes EAP phase one authentication method into JSON string
	 * @return string JSON serialized data
	 */
	public function jsonSerialize(): string {
		return (string) $this->toScalar();
	}

}
