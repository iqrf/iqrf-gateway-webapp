<?php

/**
 * TEST: App\IqrfNetModule\Enums\TrSeries
 * @covers App\IqrfNetModule\Enums\TrSeries
 * @phpVersion >= 7.2
 * @testCase
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
	 * Tests the function to create IQRF TR series enum from the IQRF TR type - (DC)TR-76D
	 */
	public function testFromTrTypeTr7xD(): void {
		$expected = TrSeries::TR_7XD();
		Assert::same($expected, TrSeries::fromTrType('(DC)TR-76Dx'));
	}

	/**
	 * Tests the function to create IQRF TR series enum from the IQRF TR type - unknown type
	 */
	public function testFromTrTypeUnknown(): void {
		Assert::exception(function (): void {
			TrSeries::fromTrType('unknown');
		}, DomainException::class);
	}

	/**
	 * Tests the function to create IQRF TR series enum from the IQRF TR type - (DC)TR-72D
	 */
	public function testFromTrMcuTypeTr72D(): void {
		$expected = TrSeries::TR_7XD();
		Assert::same($expected, TrSeries::fromTrMcuType(36));
	}

	/**
	 * Tests the function to create IQRF TR series enum from the IQRF TR type - unknown type
	 */
	public function testFromTrMcuTypeUnknown(): void {
		Assert::exception(function (): void {
			TrSeries::fromTrMcuType(0);
		}, DomainException::class);
	}

	/**
	 * Tests the function to create IQRF TR series enum from IQRF OS diff file name - (DC)TR-76D
	 */
	public function testFromIqrfOsFileName7xD(): void {
		$expected = TrSeries::TR_7XD();
		Assert::same($expected, TrSeries::fromIqrfOsFileName('TR7x'));
	}

	/**
	 * Tests the function to create IQRF TR series enum from IQRF OS diff file name - unknown type
	 */
	public function testFromIqrfOsFileNameUnknown(): void {
		Assert::exception(function (): void {
			TrSeries::fromIqrfOsFileName('unknown');
		}, DomainException::class);
	}

}

$test = new TrSeriesTest();
$test->run();
