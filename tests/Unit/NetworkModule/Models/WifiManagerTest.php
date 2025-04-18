<?php

/**
 * TEST: App\NetworkModule\Models\WifiManager
 * @covers App\NetworkModule\Models\WifiManager
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

namespace Tests\Unit\NetworkModule\Models;

use App\NetworkModule\Entities\WifiNetwork;
use App\NetworkModule\Enums\WifiMode;
use App\NetworkModule\Enums\WifiSecurity;
use App\NetworkModule\Exceptions\NetworkManagerException;
use App\NetworkModule\Models\WifiManager;
use Iqrf\CommandExecutor\Tester\Traits\CommandExecutorTestCase;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for WiFi network manager
 */
final class WifiManagerTest extends TestCase {

	use CommandExecutorTestCase;

	/**
	 * NetworkManager WiFi list command
	 */
	private const LIST_COMMAND = 'nmcli -t -f IN-USE,BSSID,SSID,MODE,CHAN,RATE,SIGNAL,SECURITY device wifi list --rescan auto';

	/**
	 * @var WifiManager WiFi network manager
	 */
	private WifiManager $manager;

	/**
	 * Tests the function for listing available WiFi networks
	 */
	public function testList(): void {
		$this->receiveCommand(
			command: self::LIST_COMMAND,
			needSudo: true,
			stdout: <<<'EOT'
*:04\:F0\:21\:24\:1E\:53:WIFI MAGDA:Infra:36:405 Mbit/s:60:WPA2
 :18\:E8\:29\:E4\:CB\:9A:WIFI MAGDA:Infra:1:195 Mbit/s:47:WPA2
 :1A\:E8\:29\:E5\:CB\:9A:WIFI MAGDA:Infra:36:405 Mbit/s:32:WPA2
EOT,
		);
		$ssid = 'WIFI MAGDA';
		$mode = WifiMode::INFRA;
		$security = WifiSecurity::WPA2_PERSONAL;
		$expected = [
			new WifiNetwork(true, '04:F0:21:24:1E:53', $ssid, $mode, 36, '405 Mbit/s', 60, $security),
			new WifiNetwork(false, '18:E8:29:E4:CB:9A', $ssid, $mode, 1, '195 Mbit/s', 47, $security),
			new WifiNetwork(false, '1A:E8:29:E5:CB:9A', $ssid, $mode, 36, '405 Mbit/s', 32, $security),
		];
		Assert::equal($expected, $this->manager->list());
	}

	/**
	 * Tests the function for listing available WiFi networks - NetworkManager error
	 */
	public function testListError(): void {
		Assert::throws(function (): void {
			$this->receiveCommand(
				command: self::LIST_COMMAND,
				needSudo: true,
				stderr: 'ERROR',
				exitCode: 1,
			);
			$this->manager->list();
		}, NetworkManagerException::class, 'ERROR');
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->setUpCommandExecutor();
		$this->manager = new WifiManager($this->commandExecutor);
	}

}

$test = new WifiManagerTest();
$test->run();
