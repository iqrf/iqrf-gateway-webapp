<?php

/**
 * TEST: App\NetworkModule\Entities\VlanConfiguration
 * @covers App\NetworkModule\Entities\VlanConfiguration
 * @phpVersion >= 8.2
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

use App\NetworkModule\Entities\VlanConfiguration;
use App\NetworkModule\Entities\VlanFlags;
use App\NetworkModule\Utils\NmCliConnection;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for VLAN configuration entity
 */
final class VlanConfigurationTest extends TestCase {

	/**
	 * NetworkManager data directory
	 */
	private const NM_DATA = TESTER_DIR . '/data/networkManager/';

	/**
	 * Parent Ethernet interface
	 */
	private const PARENT_INTERFACE = 'enp10s0';

	/**
	 * VLAN ID
	 */
	private const VLAN_ID = 10;

	/**
	 * JSON serialized VLAN configuration
	 */
	private const JSON = [
		'parentInterface' => 'enp10s0',
		'id' => 10,
		'flags' => [
			'reorderHeaders' => true,
			'gvrp' => false,
			'looseBinding' => false,
			'mvrp' => false,
		],
	];

	/**
	 * @var VlanConfiguration VLAN configuration entity
	 */
	private VlanConfiguration $entity;

	/**
	 * @var VlanFlags VLAN flags
	 */
	private VlanFlags $flags;

	/**
	 * Tests the function to deserialize JSON into VLAN configuration entity
	 */
	public function testJsonDeserialize(): void {
		$connection = ArrayHash::from(self::JSON, true);
		Assert::equal($this->entity, VlanConfiguration::jsonDeserialize($connection));
	}

	/**
	 * Tests the function to serialize VLAN configuration entity into JSON
	 */
	public function testJsonSerialize(): void {
		Assert::same(self::JSON, $this->entity->jsonSerialize());
	}

	/**
	 * Tests the function to create a new VLAN configuration entity from nmcli connection configuration
	 */
	public function testNmCliDeserialize(): void {
		$connection = FileSystem::read(self::NM_DATA . '8c00633c-c649-4288-b69e-a6715c6ddd78.conf');
		$connection = NmCliConnection::decode($connection);
		Assert::equal($this->entity, VlanConfiguration::nmCliDeserialize($connection));
	}

	/**
	 * Tests the function to serialize VLAN configuration entity to nmcli connection configuration
	 */
	public function testNmCliSerialize(): void {
		$expected = sprintf('vlan.parent "%s" vlan.id "%s" vlan.flags "%s" ', self::PARENT_INTERFACE, self::VLAN_ID, 1);
		Assert::same($expected, $this->entity->nmCliSerialize());
	}

	/**
	 * Sets up the testing environment
	 */
	protected function setUp(): void {
		$this->flags = new VlanFlags(true, false, false, false);
		$this->entity = new VlanConfiguration(self::PARENT_INTERFACE, self::VLAN_ID, $this->flags);
	}

}

$test = new VlanConfigurationTest();
$test->run();
