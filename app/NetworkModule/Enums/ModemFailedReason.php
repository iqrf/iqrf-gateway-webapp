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

/**
 * GSM modem failed reason
 */
enum ModemFailedReason: string {

	/// eSIM is not initialized
	case ESIM_WITHOUT_PROFILES = 'esim-without-profiles';
	/// No error
	case NONE = 'none';
	/// SIM is available, but unusable (e.g. permanently locked)
	case SIM_ERROR = 'sim-error';
	/// SIM is required but missing
	case SIM_MISSING = 'sim-missing';
	/// Unknown error
	case UNKNOWN = 'unknown';
	/// Unknown modem capabilities
	case UNKNOWN_CAPABILITIES = 'unknown-capabilities';

}
