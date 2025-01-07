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

namespace App\CoreModule\Enums;

/**
 * Session expiration enum
 */
enum SessionExpiration: string {

	/**
	 * Default expiration (90 minutes)
	 */
	case Default = 'default';

	/**
	 * Expiration: day
	 */
	case Day = 'day';

	/**
	 * Expiration: week
	 */
	case Week = 'week';

	/**
	 * Returns a PHP date modification string
	 * @return string PHP date modification string
	 */
	public function toDateModify(): string {
		return match ($this) {
			self::Default => '+90 min',
			self::Day => '+1 day',
			self::Week => '+1 week',
		};
	}

}
