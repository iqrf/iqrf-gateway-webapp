<?php

/**
 * TEST: App\NetworkModule\Entities\WifiConnectionSecurity
 * @covers App\NetworkModule\Entities\WifiConnectionSecurity
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
	private WifiConnectionSecurity $entityPsk;

	/**
	 * @var WifiConnectionSecurity WiFi connection security entity
	 */
	private WifiConnectionSecurity $entityLeap;

	/**
	 * @var WifiConnectionSecurity WiFi connection security entity
	 */
	private WifiConnectionSecurity $entityWep;

	public function testJsonSerializePsk(): void {
		$expected = [
			'type' => 'wpa-psk',
			'psk' => 'password',
		];
		Assert::same($expected, $this->entityPsk->jsonSerialize());
	}

	public function testJsonSerializeLeap(): void {
		$expected = [
			'type' => 'leap',
			'leap' => [
				'username' => '',
				'password' => '',
			],
		];
		Assert::same($expected, $this->entityLeap->jsonSerialize());
	}

	public function testJsonSerializeWep(): void {
		$expected = [
			'type' => 'wep',
			'wep' => [
				'type' => 'unknown',
				'index' => 0,
				'keys' => ['', '', '', ''],
			],
		];
		Assert::same($expected, $this->entityWep->jsonSerialize());
	}

	/**
	 * Sets up the testing environment
	 */
	protected function setUp(): void {
		$leap = new Leap('', '');
		$wep = new Wep(WepKeyType::UNKNOWN, 0, ['', '', '', '']);
		$psk = 'password';
		$this->entityPsk = new WifiConnectionSecurity(WifiSecurityType::WPA_PSK, $psk, null, null, null);
		$this->entityLeap = new WifiConnectionSecurity(WifiSecurityType::LEAP, null, $leap, null, null);
		$this->entityWep = new WifiConnectionSecurity(WifiSecurityType::WEP, null, null, $wep, null);
	}

}

$test = new WifiConnectionSecurityTest();
$test->run();
