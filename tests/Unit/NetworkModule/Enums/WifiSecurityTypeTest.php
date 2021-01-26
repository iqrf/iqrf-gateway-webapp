<?php

/**
 * TEST: App\NetworkModule\Enums\WifiSecurityType
 * @covers App\NetworkModule\Enums\WifiSecurityType
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Enums;

use App\NetworkModule\Enums\WifiSecurityType;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for WiFi security type enum
 */
final class WifiSecurityTypeTest extends TestCase {

	/**
	 * Tests the function to deserialize WiFi security type from nmcli configuration string - Open
	 */
	public function testNmCliDeserializeOpen(): void {
		$expected = WifiSecurityType::OPEN();
		$nmCli = '';
		$actual = WifiSecurityType::nmCliDeserialize($nmCli);
		Assert::same($expected, $actual);
	}

	/**
	 * Tests the function to deserialize WiFi security type from nmcli configuration string - Cisco LEAP
	 */
	public function testNmCliDeserializeLeap(): void {
		$expected = WifiSecurityType::LEAP();
		$nmCli = '802-11-wireless-security.key-mgmt:ieee8021x' .
			PHP_EOL . '802-11-wireless-security.auth-alg:leap';
		$actual = WifiSecurityType::nmCliDeserialize($nmCli);
		Assert::same($expected, $actual);
	}

	/**
	 * Tests the function to deserialize WiFi security type from nmcli configuration string - WEP
	 */
	public function testNmCliDeserializeWep(): void {
		$expected = WifiSecurityType::WEP();
		$nmCli = '802-11-wireless-security.key-mgmt:none' .
			PHP_EOL . '802-11-wireless-security.auth-alg:open';
		$actual = WifiSecurityType::nmCliDeserialize($nmCli);
		Assert::same($expected, $actual);
	}

	/**
	 * Tests the function to deserialize WiFi security type from nmcli configuration string - WPA-EAP
	 */
	public function testNmCliDeserializeWpaEap(): void {
		$expected = WifiSecurityType::WPA_EAP();
		$nmCli = '802-11-wireless-security.key-mgmt:wpa-eap';
		$actual = WifiSecurityType::nmCliDeserialize($nmCli);
		Assert::same($expected, $actual);
	}

	/**
	 * Tests the function to deserialize WiFi security type from nmcli configuration string - WPA-PSK
	 */
	public function testNmCliDeserializeWpaPsk(): void {
		$expected = WifiSecurityType::WPA_PSK();
		$nmCli = '802-11-wireless-security.key-mgmt:wpa-psk';
		$actual = WifiSecurityType::nmCliDeserialize($nmCli);
		Assert::same($expected, $actual);
	}

	/**
	 * Tests the function to serialize WiFi security type into nmcli configuration string - Open
	 */
	public function testNmCliSerializeOpen(): void {
		$expected = '';
		$type = WifiSecurityType::OPEN();
		Assert::same($expected, $type->nmCliSerialize());
	}

	/**
	 * Tests the function to serialize WiFi security type into nmcli configuration string - Cisco LEAP
	 */
	public function testNmCliSerializeLeap(): void {
		$expected = '802-11-wireless-security.key-mgmt "ieee8021x" 802-11-wireless-security.auth-alg "leap" ';
		$type = WifiSecurityType::LEAP();
		Assert::same($expected, $type->nmCliSerialize());
	}

	/**
	 * Tests the function to serialize WiFi security type into nmcli configuration string - WEP
	 */
	public function testNmCliSerializeWep(): void {
		$expected = '802-11-wireless-security.key-mgmt "none" 802-11-wireless-security.auth-alg "open" ';
		$type = WifiSecurityType::WEP();
		Assert::same($expected, $type->nmCliSerialize());
	}

	/**
	 * Tests the function to serialize WiFi security type into nmcli configuration string - WPA-EAP
	 */
	public function testNmCliSerializeWpaEap(): void {
		$expected = '802-11-wireless-security.key-mgmt "wpa-eap" ';
		$type = WifiSecurityType::WPA_EAP();
		Assert::same($expected, $type->nmCliSerialize());
	}

	/**
	 * Tests the function to serialize WiFi security type into nmcli configuration string - WPA-PSK
	 */
	public function testNmCliSerializeWpaPsk(): void {
		$expected = '802-11-wireless-security.key-mgmt "wpa-psk" ';
		$type = WifiSecurityType::WPA_PSK();
		Assert::same($expected, $type->nmCliSerialize());
	}

}

$test = new WifiSecurityTypeTest();
$test->run();
