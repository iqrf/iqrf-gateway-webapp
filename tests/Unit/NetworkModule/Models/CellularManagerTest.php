<?php

/**
 * TEST: App\NetworkModule\Models\CellularManager
 * @covers App\NetworkModule\Models\CellularManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

use App\NetworkModule\Models\CellularManager;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Cellular manager
 */
final class CellularManagerTest extends CommandTestCase {

	/**
	 * @var CellularManager GSM manager
	 */
	private CellularManager $manager;

	/**
	 * Tests the function to scan GSM modems
	 */
	public function testScanModems(): void {
		$this->receiveCommand('mmcli --scan-modems', true, 'successfully requested to scan devices');
		Assert::noError(function (): void {
			$this->manager->scanModems();
		});
	}

	/**
	 * Tests the function to list GSM modems
	 */
	public function testListModems(): void {
		$stdout = '{"modem-list":["/org/freedesktop/ModemManager1/Modem/3"]}';
		$this->receiveCommand('mmcli --list-modems --output-json', true, $stdout);
		$stdout = FileSystem::read(TESTER_DIR . '/data/modemManager/connected-3g.json');
		$this->receiveCommand('mmcli -m /org/freedesktop/ModemManager1/Modem/3 --output-json', true, $stdout);
		$stdout = '{"modem":{"signal":{"5g":{"error-rate":"--","rsrp":"--","rsrq":"--","snr":"--"},"cdma1x":{"ecio":"--","error-rate":"--","rssi":"--"},"evdo":{"ecio":"--","error-rate":"--","io":"--","rssi":"--","sinr":"--"},"gsm":{"error-rate":"--","rssi":"--"},"lte":{"error-rate":"--","rsrp":"--","rsrq":"--","rssi":"--","snr":"--"},"refresh":{"rate":"0"},"threshold":{"error-rate":"no","rssi":"0"},"umts":{"ecio":"--","error-rate":"--","rscp":"--","rssi":"--"}}}}';
		$this->receiveCommand('mmcli -m /org/freedesktop/ModemManager1/Modem/3 --signal-get --output-json', true, $stdout);
		$stdout = 'Successfully setup signal quality information polling';
		$this->receiveCommand('mmcli -m /org/freedesktop/ModemManager1/Modem/3 --signal-setup=300', true, $stdout);
		$stdout = '{"modem":{"signal":{"5g":{"error-rate":"--","rsrp":"--","rsrq":"--","snr":"--"},"cdma1x":{"ecio":"--","error-rate":"--","rssi":"--"},"evdo":{"ecio":"--","error-rate":"--","io":"--","rssi":"--","sinr":"--"},"gsm":{"error-rate":"--","rssi":"-67,00"},"lte":{"error-rate":"--","rsrp":"--","rsrq":"--","rssi":"--","snr":"--"},"refresh":{"rate":"300"},"threshold":{"error-rate":"no","rssi":"0"},"umts":{"ecio":"--","error-rate":"--","rscp":"--","rssi":"--"}}}}';
		$this->receiveCommand('mmcli -m /org/freedesktop/ModemManager1/Modem/3 --signal-get --output-json', true, $stdout);
		$actual = $this->manager->listModems();
		$expected = [
			[
				'interface' => 'ttyUSB2',
				'imei' => '862570024875048',
				'manufacturer' => 'huawei',
				'model' => 'E3131',
				'state' => 'connected',
				'failedReason' => null,
				'signal' => 74,
				'rssi' => -67.0,
			],
		];
		Assert::equal($expected, $actual);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new CellularManager($this->commandManager);
	}

}
$test = new CellularManagerTest();
$test->run();
