<?php

/**
 * TEST: App\NetworkModule\Entities\Modem
 * @covers App\NetworkModule\Entities\Modem
 * @phpVersion >= 7.3
 * @testCase
 */
/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

use App\NetworkModule\Entities\Modem;
use App\NetworkModule\Enums\ModemStates;
use stdClass;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Modem entity
 */
final class ModemTest extends TestCase {

	/**
	 * Manufacturer
	 */
	private const MANUFACTURER = 'manufacturer';

	/**
	 * Model
	 */
	private const MODEL = 'model';

	/**
	 * Equipment ID
	 */
	private const IMEI = 000000000000000;

	/**
	 * Network interface
	 */
	private const NETWORK_INTERFACE = 'ttyUSB0';

	/**
	 * Connected state
	 */
	private const STATE = 'connected';

	/**
	 * Failed state
	 */
	private const FAILED_STATE = 'failed';

	/**
	 * Failed state reason
	 */
	private const ERROR = 'sim-missing';

	/**
	 * Network operator
	 */
	private const OPERATOR = 'operator';

	/**
	 * Signal strength
	 */
	private const SIGNAL = 75;

	/**
	 * Access technology
	 */
	private const TECHNOLOGY = 'edge';

	/**
	 * RSSI
	 */
	private const RSSI = -55.0;

	/**
	 * Returns list of test data for testFromMmcliJson() method
	 * @return array<array<array<stdClass>|Modem>> List of test data for testFromMmcliJson() method
	 */
	public function getJsonToModemEntityData(): array {
		return [
			[
				[
					(object) [
						'modem' => (object) [
							'3gpp' => (object) [
								'operator-name' => self::OPERATOR,
							],
							'generic' => (object) [
								'manufacturer' => self::MANUFACTURER,
								'model' => self::MODEL,
								'equipment-identifier' => self::IMEI,
								'primary-port' => self::NETWORK_INTERFACE,
								'state' => self::STATE,
								'signal-quality' => (object) [
									'value' => self::SIGNAL,
								],
								'access-technologies' => [
									'edge',
								],
							],
						],
					],
					(object) [
						'modem' => (object) [
							'signal' => (object) [
								'gsm' => (object) [
									'rssi' => self::RSSI,
								],
							],
						],
					],
				],
				new Modem(self::MANUFACTURER, self::MODEL, self::IMEI, self::NETWORK_INTERFACE, ModemStates::fromScalar(self::STATE), null, self::OPERATOR, self::SIGNAL, self::TECHNOLOGY, self::RSSI),
			],
			[
				[
					(object) [
						'modem' => (object) [
							'generic' => (object) [
								'manufacturer' => self::MANUFACTURER,
								'model' => self::MODEL,
								'equipment-identifier' => self::IMEI,
								'primary-port' => self::NETWORK_INTERFACE,
								'state' => self::FAILED_STATE,
								'state-failed-reason' => self::ERROR,
							],
						],
					],
					(object) [
						'modem' => (object) [
							'signal' => (object) [
								'gsm' => (object) [
									'rssi' => self::RSSI,
								],
							],
						],
					],
				],
				new Modem(self::MANUFACTURER, self::MODEL, self::IMEI, self::NETWORK_INTERFACE, ModemStates::fromScalar(self::FAILED_STATE), self::ERROR),
			],
		];
	}

	/**
	 * Returns list of test data for testJsonSerialize() method
	 * @return array<array<Modem|array<string, float|int|string>>> List of test data for testJsonSerialize() method
	 */
	public function getModemEntityToJsonData(): array {
		return [
			[
				new Modem(self::MANUFACTURER, self::MODEL, self::IMEI, self::NETWORK_INTERFACE, ModemStates::fromScalar(self::STATE), null, self::OPERATOR, self::SIGNAL, self::TECHNOLOGY, self::RSSI),
				[
					'manufacturer' => self::MANUFACTURER,
					'model' => self::MODEL,
					'imei' => self::IMEI,
					'interface' => self::NETWORK_INTERFACE,
					'state' => self::STATE,
					'operator' => self::OPERATOR,
					'signal' => self::SIGNAL,
					'technology' => self::TECHNOLOGY,
					'rssi' => self::RSSI,
				],
			],
			[
				new Modem(self::MANUFACTURER, self::MODEL, self::IMEI, self::NETWORK_INTERFACE, ModemStates::fromScalar(self::FAILED_STATE), self::ERROR),
				[
					'manufacturer' => self::MANUFACTURER,
					'model' => self::MODEL,
					'imei' => self::IMEI,
					'interface' => self::NETWORK_INTERFACE,
					'state' => self::FAILED_STATE,
					'error' => self::ERROR,
				],
			],
		];
	}

	/**
	 * Tests the function deserialize JSON into Modem entity
	 * @dataProvider getJsonToModemEntityData
	 * @param array<stdClass> $data Modem JSON data
	 * @param Modem $expected Expected Modem entity
	 */
	public function testFromMmcliJson(array $data, Modem $expected): void {
		Assert::equal($expected, Modem::fromMmcliJson($data[0], $data[1]));
	}

	/**
	 * Tests the function to serialize Modem entity to JSON
	 * @dataProvider getModemEntityToJsonData
	 * @param Modem $entity Modem entity
	 * @param array<string, float|int|string> $expected Expected modem JSON
	 */
	public function testJsonSerialize(Modem $entity, array $expected): void {
		Assert::same($expected, $entity->jsonSerialize());
	}

}

$test = new ModemTest();
$test->run();
