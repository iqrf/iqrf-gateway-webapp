<?php

/**
 * Copyright 2023-2026 IQRF Tech s.r.o.
 * Copyright 2023-2026 MICRORISC s.r.o.
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

use JsonSerializable;

/**
 * API key state
 */
enum ApiKeyState: int implements JsonSerializable {

	/// Active key
	case Active = 0;

	/// Revoked key
	case Revoked = 1;

	/// Default user state
	final public const Default = self::Active;

	/**
	 * Is api key active?
	 * @return bool true if api key is active, false otherwise
	 */
	public function isActive(): bool {
		return $this === self::Active;
	}

	/**
	 * Revoke the api key
	 * @return ApiKeyState API key state
	 */
	public function revoke(): self {
		return self::Revoked;
	}

	/**
	 * Returns the user state as a string
	 * @return string User state as a string
	 */
	public function toString(): string {
		return match ($this) {
			self::Active => 'active',
			self::Revoked => 'revoked',
		};
	}

	/**
	 * Serialize the user state into JSON
	 * @return string User state as a string
	 */
	public function jsonSerialize(): string {
		return $this->toString();
	}

}
