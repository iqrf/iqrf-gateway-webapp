<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

namespace App\ConfigModule\Enums;

use Grifart\Enum\AutoInstances;
use Grifart\Enum\Enum;

/**
 * Configuration device type enum
 * @method static DeviceTypes ADAPTER()
 * @method static DeviceTypes BOARD()
 */
final class DeviceTypes extends Enum {

	use AutoInstances;

	/**
	 * @var string Adapter device
	 */
	private const ADAPTER = 'adapter';

	/**
	 * @var string Board device
	 */
	private const BOARD = 'board';

	/**
	 * Configuration device type into JSON string
	 * @return string JSON serialized string
	 */
	public function jsonSerialize(): string {
		return (string) $this->toScalar();
	}

}
