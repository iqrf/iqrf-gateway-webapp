<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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

namespace App\IqrfNetModule\Enums;

use DomainException;
use Grifart\Enum\AutoInstances;
use Grifart\Enum\Enum;
use Nette\Utils\Strings;

/**
 * IQRF TR series enum
 * @method static TrSeries TR_7XD();
 */
final class TrSeries extends Enum {

	use AutoInstances;

	/**
	 * IQRF (DC)TR-7xD
	 */
	private const TR_7XD = '7xD';

	/**
	 * Creates IQRF TR series enum from the IQRF TR type
	 * @param string $trType IQRF TR type
	 * @return TrSeries IQRF TR series enum
	 */
	public static function fromTrType(string $trType): self {
		if (Strings::match($trType, '~(\(DC\))?TR-7[0-9]Dx~') !== null) {
			return self::TR_7XD();
		}
		throw new DomainException();
	}

}
