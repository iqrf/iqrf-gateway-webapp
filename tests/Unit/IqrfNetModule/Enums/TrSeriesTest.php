<?php

/**
 * TEST: App\IqrfNetModule\Enums\TrSeries
 * @covers App\IqrfNetModule\Enums\TrSeries
 * @phpVersion >= 7.4
 * @testCase
 */
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

namespace Tests\Unit\IqrfNetModule\Enums;

use App\IqrfNetModule\Enums\TrSeries;
use DomainException;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests IQRF TR series enum
 */
final class TrSeriesTest extends TestCase {

	/**
	 * Returns TR series strings and corresponding TrSeries enum value
	 * @return array<array<string|TrSeries>> TR series strings and values
	 */
	public function getTrSeriesStringData(): array {
		return [
			['(DC)TR-76Dx', TrSeries::TR_7XD],
			['TR-76Dx', TrSeries::TR_7XD],
			['(DC)TR-72Gx', TrSeries::TR_7XG],
		];
	}

	/**
	 * Returns TR/MCU value and corresponding TrSeries enum value
	 * @return array<array<int|TrSeries>> TR/MCU numerical and enum values
	 */
	public function getTrMcuNumericalData(): array {
		return [
			[36, TrSeries::TR_7XD],
			[37, TrSeries::TR_7XG],
		];
	}

	/**
	 * Returns OS file name TR series value and corresponding TrSeries enum value
	 * @return array<array<string|TrSeries>> OS file TR series and enum values
	 */
	public function getOsFileTrData(): array {
		return [
			['TR7x', TrSeries::TR_7XD],
			['TR7xD', TrSeries::TR_7XD],
			['TR7xG', TrSeries::TR_7XG],
		];
	}

	/**
	 * Tests the function to create IQRF TR series enum from the IQRF TR type string
	 * @dataProvider getTrSeriesStringData
	 * @param string $trSeries TR series string
	 * @param TrSeries $enumValue TR Series enum value
	 */
	public function testFromTrType(string $trSeries, TrSeries $enumValue): void {
		Assert::same($enumValue, TrSeries::fromTrType($trSeries));
	}

	/**
	 * Tests the function to create IQRF TR series enum from unknown IQRF TR type string
	 */
	public function testFromTrTypeUnknown(): void {
		Assert::exception(static function (): void {
			TrSeries::fromTrType('unknown');
		}, DomainException::class);
	}

	/**
	 * Tests the function to create IQRF TR series enum from the IQRF TR type numerical value
	 * @dataProvider getTrMcuNumericalData
	 * @param int $trMcuType TR/MCU type numerical value
	 * @param TrSeries $enumValue TR Series enum value
	 */
	public function testFromTrMcuType(int $trMcuType, TrSeries $enumValue): void {
		Assert::same($enumValue, TrSeries::fromTrMcuType($trMcuType));
	}

	/**
	 * Tests the function to create IQRF TR series enum from the IQRF TR type unknown value
	 */
	public function testFromTrMcuTypeUnknown(): void {
		Assert::exception(static function (): void {
			TrSeries::fromTrMcuType(0);
		}, DomainException::class);
	}

	/**
	 * Tests the function to create IQRF TR series enum from IQRF OS diff file name
	 * @dataProvider getOsFileTrData
	 * @param string $trSeries OS file TR series value
	 * @param TrSeries $enumValue TR Series enum value
	 */
	public function testFromIqrfOsFileName(string $trSeries, TrSeries $enumValue): void {
		Assert::same($enumValue, TrSeries::fromIqrfOsFileName($trSeries));
	}

	/**
	 * Tests the function to create IQRF TR series enum from IQRF OS diff file name - unknown type
	 */
	public function testFromIqrfOsFileNameUnknown(): void {
		Assert::exception(static function (): void {
			TrSeries::fromIqrfOsFileName('unknown');
		}, DomainException::class);
	}

}

$test = new TrSeriesTest();
$test->run();
