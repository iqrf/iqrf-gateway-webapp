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

namespace App\SecurityModule\Enums;

/**
 * Mosquitto plugin manager status code enum
 */
enum MosquittoPluginManagerStatusCodes: int {

	/**
	 * General or unexpected error
	 */
	case GENERAL_ERROR = 1;
	/**
	 * Invoked with unknown or invalid command
	 */
	case UNKNOWN_COMMAND = 2;
	/**
	 * Invoked with invalid parameters
	 */
	case INVALID_PARAMS = 3;
	/**
	 * User record not found
	 */
	case USER_NOT_FOUND = 4;
	/**
	 * User is already blocked
	 */
	case USER_BLOCKED = 5;

}
