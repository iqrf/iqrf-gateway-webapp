<?php

/**
 * TEST: App\NetworkModule\Entities\Modem
 * @covers App\NetworkModule\Entities\Modem
 * @phpVersion >= 7.4
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
use App\NetworkModule\Enums\ModemFailedReason;
use App\NetworkModule\Enums\ModemState;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Modem entity
 */
final class ModemTest extends TestCase {

	/**
	 * @var string Network interface
	 */
	private const NETWORK_INTERFACE = 'ttyAMA2';

	/**
	 * @var string Modem IMEI
	 */
	private const IMEI = '865167066186454';

	/**
	 * @var string Modem manufacturer
	 */
	private const MANUFACTURER = 'Quectel';

	/**
	 * @var string Modem model
	 */
	private const MODEL = 'EG25';

	/**
	 * @var int Signal strength
	 */
	private const SIGNAL = 60;

	/**
	 * @var float RSSI
	 */
	private const RSSI = -55.0;

	/**
	 * @var Modem Modem entity
	 */
	private Modem $entity;

	/**
	 * @var ModemState Modem state
	 */
	private ModemState $state;

	/**
	 * @var ModemFailedReason|null Modem failed reason
	 */
	private ?ModemFailedReason $failedReason = null;

	/**
	 * Sets up the testing environment
	 */
	protected function setUp(): void {
		$this->state = ModemState::CONNECTED();
		$this->entity = new Modem(self::NETWORK_INTERFACE, self::IMEI, self::MANUFACTURER, self::MODEL, $this->state, $this->failedReason, self::SIGNAL);
		$this->entity->setRssi(self::RSSI);
	}

	/**
	 * Tests the function to create a new Modem entity from mmcli JSON object
	 */
	public function testFromMmcliJson(): void {
		$modem = Json::decode(FileSystem::read(TESTER_DIR . '/data/modemManager/connected.json'));
		$rssi = ArrayHash::from([
			'modem' => [
				'signal' => [
					'gsm' => [
						'rssi' => self::RSSI,
					],
				],
			],
		], true);
		Assert::equal($this->entity, Modem::fromMmcliJson($modem, $rssi));
	}

	/**
	 * Tests the function to serialize Modem entity to JSON
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'interface' => self::NETWORK_INTERFACE,
			'imei' => self::IMEI,
			'manufacturer' => self::MANUFACTURER,
			'model' => self::MODEL,
			'state' => $this->state->toScalar(),
			'failedReason' => $this->failedReason,
			'signal' => self::SIGNAL,
			'rssi' => self::RSSI,
		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

}

$test = new ModemTest();
$test->run();
