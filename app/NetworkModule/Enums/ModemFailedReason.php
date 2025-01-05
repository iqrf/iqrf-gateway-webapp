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

/**
 * GSM modem failed reason
 * @method static ModemFailedReason NONE()
 * @method static ModemFailedReason UNKNOWN()
 * @method static ModemFailedReason SIM_MISSING()
 * @method static ModemFailedReason SIM_ERROR()
 * @method static ModemFailedReason UNKNOWN_CAPABILITIES()
 * @method static ModemFailedReason ESIM_WITHOUT_PROFILES()
 */
final class ModemFailedReason extends Enum {

	use AutoInstances;

	/**
	 * @var string eSIM is not initialized
	 */
	private const ESIM_WITHOUT_PROFILES = 'esim-without-profiles';

	/**
	 * @var string No error
	 */
	private const NONE = 'none';

	/**
	 * @var string SIM is available, but unusable (e.g. permanently locked)
	 */
	private const SIM_ERROR = 'sim-error';

	/**
	 * @var string SIM is required but missing
	 */
	private const SIM_MISSING = 'sim-missing';

	/**
	 * @var string Unknown error
	 */
	private const UNKNOWN = 'unknown';

	/**
	 * @var string Unknown modem capabilities
	 */
	private const UNKNOWN_CAPABILITIES = 'unknown-capabilities';

}
