<?php

/**
 * TEST: App\NetworkModule\Enums\WifiSecurity
 * @covers App\NetworkModule\Enums\WifiSecurity
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Enums;

use App\NetworkModule\Enums\WifiSecurity;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for WiFi security enum
 */
final class WifiSecurityTest extends TestCase {

	/**
	 * Tests the function for creating WiFi security enum - OWE
	 */
	public function testFromNmCliOwe(): void {
		$expected = WifiSecurity::OWE();
		Assert::equal($expected, WifiSecurity::fromNmCli('OWE'));
	}

	/**
	 * Tests the function for creating WiFi security enum - WEP
	 */
	public function testFromNmCliWep(): void {
		$expected = WifiSecurity::WEP();
		Assert::equal($expected, WifiSecurity::fromNmCli('WEP'));
	}

	/**
	 * Tests the function for creating WiFi security enum - WPA3-Enterprise
	 */
	public function testFromNmCliWpa3Enterprise(): void {
		$expected = WifiSecurity::WPA3_ENTERPRISE();
		Assert::equal($expected, WifiSecurity::fromNmCli('WPA3 802.1X'));
	}

	/**
	 * Tests the function for creating WiFi security enum - WPA3-Personal
	 */
	public function testFromNmCliWpa3Personal(): void {
		$expected = WifiSecurity::WPA3_PERSONAL();
		Assert::equal($expected, WifiSecurity::fromNmCli('WPA3'));
	}

	/**
	 * Tests the function for creating WiFi security enum - WPA2-Enterprise
	 */
	public function testFromNmCliWpa2Enterprise(): void {
		$expected = WifiSecurity::WPA2_ENTERPRISE();
		Assert::equal($expected, WifiSecurity::fromNmCli('WPA2 802.1X'));
	}

	/**
	 * Tests the function for creating WiFi security enum - WPA2-Personal
	 */
	public function testFromNmCliWpa2Personal(): void {
		$expected = WifiSecurity::WPA2_PERSONAL();
		Assert::equal($expected, WifiSecurity::fromNmCli('WPA2'));
	}

	/**
	 * Tests the function for creating WiFi security enum - WPA-Enterprise
	 */
	public function testFromNmCliWpaEnterprise(): void {
		$expected = WifiSecurity::WPA_ENTERPRISE();
		Assert::equal($expected, WifiSecurity::fromNmCli('WPA 802.1X'));
	}

	/**
	 * Tests the function for creating WiFi security enum - WPA-Personal
	 */
	public function testFromNmCliWpaPersonal(): void {
		$expected = WifiSecurity::WPA_PERSONAL();
		Assert::equal($expected, WifiSecurity::fromNmCli('WPA'));
	}

	/**
	 * Tests the function for creating WiFi security enum - Open
	 */
	public function testFromNmCliOpen(): void {
		$expected = WifiSecurity::OPEN();
		Assert::equal($expected, WifiSecurity::fromNmCli(''));
	}

}

$test = new WifiSecurityTest();
$test->run();
