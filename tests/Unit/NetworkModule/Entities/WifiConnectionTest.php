<?php

/**
 * TEST: App\NetworkModule\Entities\WifiConnection
 * @covers App\NetworkModule\Entities\WifiConnection
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Entities;

use App\NetworkModule\Entities\WifiConnection;
use App\NetworkModule\Entities\WifiConnectionSecurity;
use App\NetworkModule\Entities\WifiSecurity\Leap;
use App\NetworkModule\Entities\WifiSecurity\Wep;
use App\NetworkModule\Enums\WepKeyType;
use App\NetworkModule\Enums\WifiMode;
use App\NetworkModule\Enums\WifiSecurityType;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for WiFi connection entity
 */
final class WifiConnectionTest extends TestCase {

	/**
	 * NetworkManager data directory
	 */
	private const NM_DATA = __DIR__ . '/../../../data/networkManager/';

	/**
	 * @var WifiConnection WiFi connection entity
	 */
	private $entity;

	/**
	 * Sets up the testing environment
	 */
	protected function setUp(): void {
		$ssid = 'WIFI MAGDA';
		$mode = WifiMode::INFRA();
		$leap = new Leap('', '');
		$wep = new Wep(WepKeyType::UNKNOWN(), 0, ['', '', '', '']);
		$security = new WifiConnectionSecurity(WifiSecurityType::WPA_PSK(), 'password', $leap, $wep, null);
		$this->entity = new WifiConnection($ssid, $mode, $security);
	}

	/**
	 * Tests the function to create a new IPv6 connection entity from nmcli connection configuration
	 */
	public function testNmCliDeserialize(): void {
		$configuration = FileSystem::read(self::NM_DATA . '5c7010a8-88f6-48e6-8ab2-5ad713217831.conf');
		Assert::equal($this->entity, WifiConnection::nmCliDeserialize($configuration));
	}

	/**
	 * Tests the function to return JSON serialized data
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'ssid' => 'WIFI MAGDA',
			'mode' => 'infrastructure',
			'security' => [
				'type' => 'wpa-psk',
				'psk' => 'password',
				'leap' => [
					'username' => '',
					'password' => '',
				],
				'wep' => [
					'type' => 'unknown',
					'index' => 0,
					'keys' => ['', '', '', ''],
				],
			],
		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

	/**
	 * Tests the function to convert WiFi connection entity to nmcli configuration string
	 */
	public function testNmCliSerialize(): void {
		$expected = '802-11-wireless.ssid "WIFI MAGDA" 802-11-wireless.mode "infrastructure" 802-11-wireless-security.key-mgmt "wpa-psk" 802-11-wireless-security.psk "password" 802-11-wireless-security.leap-password "" 802-11-wireless-security.leap-username "" 802-11-wireless-security.wep-key-type "unknown" 802-11-wireless-security.wep-tx-keyidx "0" 802-11-wireless-security.wep-key0 "" 802-11-wireless-security.wep-key1 "" 802-11-wireless-security.wep-key2 "" 802-11-wireless-security.wep-key3 "" ';
		Assert::same($expected, $this->entity->nmCliSerialize());
	}

}

$test = new WifiConnectionTest();
$test->run();
