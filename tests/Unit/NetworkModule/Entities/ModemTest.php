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
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Modem entity
 */
final class ModemTest extends TestCase {

	/**
	 * Network interface
	 */
	private const NETWORK_INTERFACE = 'cdc-wdm0';

	/**
	 * Signal strength
	 */
	private const SIGNAL = 75;

	/**
	 * @var Modem Modem entity
	 */
	private $entity;

	/**
	 * Sets up the testing environment
	 */
	protected function setUp(): void {
		$this->entity = new Modem(self::NETWORK_INTERFACE, self::SIGNAL);
	}

	/**
	 * Tests the function to create a new Modem entity from mmcli JSON object
	 */
	public function testFromMmcliJson(): void {
		$object = ArrayHash::from([
			'modem' => [
				'generic' => [
					'primary-port' => self::NETWORK_INTERFACE,
					'signal-quality' => [
						'value' => self::SIGNAL,
					],
				],
			],
		], true);
		Assert::equal($this->entity, Modem::fromMmcliJson($object));
	}

	/**
	 * Tests the function to serialize Modem entity to JSON
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'interface' => self::NETWORK_INTERFACE,
			'signal' => self::SIGNAL,
		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

}

$test = new ModemTest();
$test->run();
