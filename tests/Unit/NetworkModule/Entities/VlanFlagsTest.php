<?php

/**
 * TEST: App\NetworkModule\Entities\VlanFlags
 * @covers App\NetworkModule\Entities\VlanFlags
 * @phpVersion >= 8.1
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

namespace Tests\Unit\NetworkModule\Entities;

use App\NetworkModule\Entities\VlanFlags;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for VLAN flags entity
 */
final class VlanFlagsTest extends TestCase {

	/**
	 * Returns list of test data for JSON tests
	 * @return array<int, array<VlanFlags|array{reorderHeaders: bool, gvrp: bool, looseBinding: bool, mvrp: bool}>> List of JSON test data
	 */
	public function getJsonData(): array {
		return [
			[
				new VlanFlags(false, false, false, false),
				[
					'reorderHeaders' => false,
					'gvrp' => false,
					'looseBinding' => false,
					'mvrp' => false,
				],
			],
			[
				new VlanFlags(true, false, true, false),
				[
					'reorderHeaders' => true,
					'gvrp' => false,
					'looseBinding' => true,
					'mvrp' => false,
				],
			],
			[
				new VlanFlags(true, true, true, true),
				[
					'reorderHeaders' => true,
					'gvrp' => true,
					'looseBinding' => true,
					'mvrp' => true,
				],
			],
		];
	}

	/**
	 * Returns list of test data for integer tests
	 * @return array<int, array<VlanFlags|int>> List of integer test data
	 */
	public function getIntegerData(): array {
		return [
			[
				new VlanFlags(false, false, false, false),
				0,
			],
			[
				new VlanFlags(true, false, true, false),
				5,
			],
			[
				new VlanFlags(true, true, true, true),
				15,
			],
		];
	}

	/**
	 * Tests the function for JSON deserialization
	 * @dataProvider getJsonData
	 * @param VlanFlags $entity VLAN flags entity
	 * @param array{reorderHeaders: bool, gvrp: bool, looseBinding: bool, mvrp: bool} $json JSON serialized VLAN flags
	 */
	public function testJsonDeserialize(VlanFlags $entity, array $json): void {
		Assert::equal($entity, VlanFlags::jsonDeserialize((object) $json));
	}

	/**
	 * Tests the function for JSON serialization
	 * @dataProvider getJsonData
	 * @param VlanFlags $entity VLAN flags entity
	 * @param array{reorderHeaders: bool, gvrp: bool, looseBinding: bool, mvrp: bool} $json JSON serialized VLAN flags
	 */
	public function testJsonSerialize(VlanFlags $entity, array $json): void {
		Assert::same($json, $entity->jsonSerialize());
	}

	/**
	 * Tests the function for integer deserialization
	 * @dataProvider getIntegerData
	 * @param VlanFlags $entity VLAN flags entity
	 * @param int $int VLAN flags int
	 */
	public function testNmCliDeserialize(VlanFlags $entity, int $int): void {
		Assert::equal($entity, VlanFlags::nmCliDeserialize($int));
	}

	/**
	 * Tests the function for integer serialization
	 * @dataProvider getIntegerData
	 * @param VlanFlags $entity VLAN flags entity
	 * @param int $int VLAN flags int
	 */
	public function testNmCliSerialize(VlanFlags $entity, int $int): void {
		Assert::same($int, $entity->nmCliSerialize());
	}

}

$test = new VlanFlagsTest();
$test->run();
