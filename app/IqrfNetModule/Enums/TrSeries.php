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

namespace App\IqrfNetModule\Enums;

use DomainException;
use Grifart\Enum\AutoInstances;
use Grifart\Enum\Enum;
use Nette\Utils\Strings;

/**
 * IQRF TR series enum
 * @method static TrSeries TR_7XD();
 * @method static TrSeries TR_7XG();
 */
final class TrSeries extends Enum {

	use AutoInstances;

	/**
	 * @var string IQRF (DC)TR-7xD
	 */
	private const TR_7XD = '7xD';

	/**
	 * @var string IQRF (DC)TR-7xG
	 */
	private const TR_7XG = '7xG';

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
			switch ($module) {
				case 'D':
					return self::TR_7XD();
				case 'G':
					return self::TR_7XG();
				default:
					throw new DomainException();
			}
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
			switch ($trType) {
				case 2:
				case 4:
				case 11:
				case 12:
				case 13:
					return self::TR_7XD();
				default:
					throw new DomainException();
			}
		} elseif ($mcuType === 5) {
			switch ($trType) {
				case 2:
				case 11:
				case 13:
					return self::TR_7XG();
				default:
					throw new DomainException();
			}
		}
		throw new DomainException();
	}

	/**
	 * Creates IQRF TR series enum from IQRF OS diff file name
	 * @param string $trSeries IQRF TR series
	 * @return TrSeries IQRF TR series enum
	 */
	public static function fromIqrfOsFileName(string $trSeries): self {
		switch ($trSeries) {
			case 'TR7x':
			case 'TR7xD':
				return self::TR_7XD();
			case 'TR7xG':
				return self::TR_7XG();
			default:
				throw new DomainException('Unknown or unsupported TR series ' . $trSeries);
		}
	}

}
