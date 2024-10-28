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
 * Theme preference enum
 */
enum ThemePreference: int implements JsonSerializable {

	/// Automatically select the theme based on the system settings
	case Auto = 0;
	/// Light theme
	case Light = 1;
	/// Dark theme
	case Dark = 2;

	/**
	 * Deserializes the theme preference from a string
	 * @param string $theme Theme preference as a string
	 * @return self Theme preference
	 */
	public static function fromString(string $theme): self {
		return match ($theme) {
			'auto' => self::Auto,
			'light' => self::Light,
			'dark' => self::Dark,
			default => throw new DomainException('Unknown theme preference value: ' . $theme),
		};
	}

	/**
	 * Returns the theme preference as a string
	 * @return string String representation of the theme preference
	 */
	public function toString(): string {
		return match ($this) {
			self::Auto => 'auto',
			self::Light => 'light',
			self::Dark => 'dark',
		};
	}

	/**
	 * Serializes the theme preference into JSON
	 * @return string JSON-serialized theme preference
	 */
	public function jsonSerialize(): string {
		return $this->toString();
	}

}
