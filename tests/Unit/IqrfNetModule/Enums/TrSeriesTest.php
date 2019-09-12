<?php

/**
 * TEST: App\IqrfNetModule\Enums\TrSeries
 * @covers App\IqrfNetModule\Enums\TrSeries
 * @phpVersion >= 7.1
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
class TrSeriesTest extends TestCase {

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

}

$test = new TrSeriesTest();
$test->run();
