<?php

/**
 * TEST: App\NetworkModule\Enums\WifiMode
 * @covers App\NetworkModule\Enums\WifiMode
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Enums;

use App\NetworkModule\Enums\WifiMode;
use Grifart\Enum\MissingValueDeclarationException;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for WiFi mode enum
 */
class WifiModeTest extends TestCase {

	/**
	 * Tests the function for creating WiFi mode enum - Ad-Hoc
	 */
	public function testFromNetworkListAdHoc(): void {
		$expected = WifiMode::ADHOC();
		Assert::equal($expected, WifiMode::fromNetworkList('Ad-Hoc'));
	}

	/**
	 * Tests the function for creating WiFi mode enum - Infrastructure
	 */
	public function testFromNetworkListInfrastructure(): void {
		$expected = WifiMode::INFRA();
		Assert::equal($expected, WifiMode::fromNetworkList('Infra'));
	}

	/**
	 * Tests the function for creating WiFi mode enum - Mesh
	 */
	public function testFromNetworkListMesh(): void {
		$expected = WifiMode::MESH();
		Assert::equal($expected, WifiMode::fromNetworkList('Mesh'));
	}

	/**
	 * Tests the function for creating WiFi mode enum - Unknown
	 */
	public function testFromNetworkListUnknown(): void {
		Assert::throws(function (): void {
			WifiMode::fromNetworkList('Unknown');
		}, MissingValueDeclarationException::class, 'There is no value for enum \'' . WifiMode::class . '\' and scalar value \'Unknown\'.');
	}

}

$test = new WifiModeTest();
$test->run();
