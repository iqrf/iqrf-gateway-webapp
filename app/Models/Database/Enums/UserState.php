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

use JsonSerializable;

/**
 * User state enum
 */
enum UserState: int implements JsonSerializable {

	/// Unverified user
	case Unverified = 0;

	/// Verified user
	case Verified = 1;

	/// Default user state
	final public const Default = self::Unverified;

	/**
	 * Is the user state verified?
	 * @return bool Is the user state verified?
	 */
	public function isVerified(): bool {
		return $this === self::Verified;
	}

	/**
	 * Verify the user
	 * @return UserState Verified user state
	 */
	public function verify(): self {
		return match ($this) {
			self::Unverified => self::Verified,
			self::Verified => $this,
		};
	}

	/**
	 * Unverify the user
	 * @return UserState Unverified user state
	 */
	public function unverify(): self {
		return match ($this) {
			self::Verified => self::Unverified,
			self::Unverified => $this,
		};
	}

	/**
	 * Returns the user state as a string
	 * @return string User state as a string
	 */
	public function toString(): string {
		return match ($this) {
			self::Unverified => 'unverified',
			self::Verified => 'verified',
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
