<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

namespace App\Models\Database\Enums;

use DomainException;
use JsonSerializable;

/**
 * Time format enum
 */
enum TimeFormat: int implements JsonSerializable {

	/// Automatically select the time format based on the system settings
	case Auto = 0;

	/// 12-hour clock (AM/PM)
	case Hour12 = 1;

	/// 24-hour clock
	case Hour24 = 2;

	/**
	 * Deserialize time format string
	 * @param string $format Time format as a string
	 * @thrown DomainException Thrown if the time format is unknown
	 */
	public static function fromString(string $format): self {
		return match ($format) {
			'auto' => self::Auto,
			'12h' => self::Hour12,
			'24h' => self::Hour24,
			default => throw new DomainException('Unknown time format value: ' . $format),
		};
	}

	/**
	 * Serialize time format into string
	 * @return string String representation of the time format
	 */
	public function toString(): string {
		return match ($this) {
			self::Auto => 'auto',
			self::Hour12 => '12h',
			self::Hour24 => '24h',
		};
	}

	/**
	 * Serialize time format into JSON
	 * @return string JSON-serialized time format
	 */
	public function jsonSerialize(): string {
		return $this->toString();
	}

}
