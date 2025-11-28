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

namespace App\GatewayModule\Enums;

use JsonSerializable;

/**
 * eMMC health status enum
 */
enum EmmcHealthStatus: int implements JsonSerializable {

	// Undefined state - for example when eMMC memory does not support reporting.
	case Undefined = 0;

	// Normal state - eMMC is ok and there are still some reserved blocks left.
	case Normal = 1;

	// Warning - used when more than 80% of reserved blocks are consumed.
	case Warning = 2;

	// Urgent - used when more than 90% of reserved blocks are consumed.
	case Urgent = 3;

	// default = undefined
	final public const Default = self::Undefined;

	/**
	 * Returns the eMMC health status as string
	 * @return string eMMC health status
	 */
	public function toString(): string {
		return match ($this) {
			self::Undefined => 'undefined',
			self::Normal => 'normal',
			self::Warning => 'warning',
			self::Urgent => 'urgent',
		};
	}

	/**
	 * Serialize the status to JSON
	 * @return string eMMC health status
	 */
	public function jsonSerialize(): string {
		return $this->toString();
	}

}
