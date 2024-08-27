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

namespace App\IqrfNetModule\Enums;

use DomainException;
use Nette\Utils\Strings;

/**
 * IQRF TR series enum
 */
enum TrSeries: string {

	/// IQRF (DC)TR-7xD
	case TR_7XD = '7xD';
	/// IQRF (DC)TR-7xG
	case TR_7XG = '7xG';

	/**
	 * Creates IQRF TR series enum from the IQRF TR type
	 * @param string $trType IQRF TR type
	 * @return TrSeries IQRF TR series enum
	 */
	public static function fromTrType(string $trType): self {
		$matches = Strings::match($trType, '#(\(DC\))?TR-(?P<series>[78])\d(?P<module>[DG])x#');
		if ($matches === null) {
			throw new DomainException();
		}
		$series = intval($matches['series']);
		$module = $matches['module'];
		if ($series === 7) {
			return match ($module) {
				'D' => self::TR_7XD,
				'G' => self::TR_7XG,
			};
		}
		throw new DomainException();
	}

	/**
	 * Creates IQRF TR series enum from DPA OS read TR&MCU type
	 * @param int $trMcuType DPA OS read TR&MCU type
	 * @return TrSeries IQRF TR series enum
	 */
	public static function fromTrMcuType(int $trMcuType): self {
		$mcuType = $trMcuType & 0x07;
		$trType = $trMcuType >> 4;
		if ($mcuType === 4) {
			return match ($trType) {
				2, 4, 11, 12, 13 => self::TR_7XD,
				default => throw new DomainException(),
			};
		}
		if ($mcuType === 5) {
			return match ($trType) {
				2, 11, 13 => self::TR_7XG,
				default => throw new DomainException(),
			};
		}
		throw new DomainException();
	}

	/**
	 * Creates IQRF TR series enum from IQRF OS diff file name
	 * @param string $trSeries IQRF TR series
	 * @return TrSeries IQRF TR series enum
	 */
	public static function fromIqrfOsFileName(string $trSeries): self {
		return match ($trSeries) {
			'TR7x', 'TR7xD' => self::TR_7XD,
			'TR7xG' => self::TR_7XG,
			default => throw new DomainException('Unknown or unsupported TR series ' . $trSeries),
		};
	}

}
