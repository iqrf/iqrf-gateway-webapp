<?php

declare(strict_types = 1);

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

namespace App\Models\Database\Enums;

/**
 * Mapping baud rate enum
 */
enum MappingBaudRate: int {

	/**
	 * 1 200 Bd baud rate
	 */
	case Bd1200 = 1_200;

	/**
	 * 2 400 Bd baud rate
	 */
	case Bd2400 = 2_400;

	/**
	 * 4 800 Bd baud rate
	 */
	case Bd4800 = 4_800;

	/**
	 * 9 600 Bd baud rate
	 */
	case Bd9600 = 9_600;

	/**
	 * 19 200 Bd baud rate
	 */
	case Bd19200 = 19_200;

	/**
	 * 38 400 Bd baud rate
	 */
	case Bd38400 = 38_400;

	/**
	 * 57 600 Bd baud rate
	 */
	case Bd57600 = 57_600;

	/**
	 * 115 200 Bd baud rate
	 */
	case Bd115200 = 115_200;

	/**
	 * 230 400 Bd baud rate
	 */
	case Bd230400 = 230_400;

	/**
	 * Default baud rate
	 */
	public const Default = self::Bd57600;

}
