<?php

/**
 * TEST: App\NetworkModule\Entities\WifiConnectionSecurity
 * @covers App\NetworkModule\Entities\WifiConnectionSecurity
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Entities;

use App\NetworkModule\Entities\WifiConnectionSecurity;
use App\NetworkModule\Entities\WifiSecurity\Leap;
use App\NetworkModule\Entities\WifiSecurity\Wep;
use App\NetworkModule\Enums\WepKeyType;
use App\NetworkModule\Enums\WifiSecurityType;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for WiFi connection security entity
 */
final class WifiConnectionSecurityTest extends TestCase {

	/**
	 * @var WifiConnectionSecurity WiFi connection security entity
	 */
	private $entity;

	/**
	 * Sets up the testing environment
	 */
	protected function setUp(): void {
		$leap = new Leap('', '');
		$wep = new Wep(WepKeyType::UNKNOWN(), 0, ['', '', '', '']);
		$this->entity = new WifiConnectionSecurity(WifiSecurityType::WPA_PSK(), 'password', $leap, $wep, null);
	}

	public function testJsonSerialize(): void {
		$expected = [
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
		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

}

$test = new WifiConnectionSecurityTest();
$test->run();
