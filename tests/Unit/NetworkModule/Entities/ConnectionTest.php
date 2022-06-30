<?php

/**
 * TEST: App\NetworkModule\Entities\Connection
 * @covers App\NetworkModule\Entities\Connection
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

use App\NetworkModule\Entities\Connection;
use App\NetworkModule\Enums\ConnectionTypes;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for network connection entity
 */
final class ConnectionTest extends TestCase {

	/**
	 * Network connection name
	 */
	private const NAME = 'eth0';

	/**
	 * @var UuidInterface Network connection UUID
	 */
	private UuidInterface $uuid;

	/**
	 * @var ConnectionTypes Network connection type
	 */
	private ConnectionTypes $type;

	/**
	 * Network interface name
	 */
	private const INTERFACE = 'eth0';

	/**
	 * @var Connection Network connection entity
	 */
	private Connection $entity;

	/**
	 * Sets up the test environment
	 */
	public function __construct() {
		$this->uuid = Uuid::fromString('25ab1b06-2a86-40a9-950f-1c576ddcd35a');
		$this->type = ConnectionTypes::ETHERNET();
		$this->entity = new Connection(self::NAME, $this->uuid, $this->type, self::INTERFACE);
	}

	/**
	 * Tests the function to deserialize network connection entity from nmcli row
	 */
	public function testNmCliDeserialize(): void {
		$string = 'eth0:25ab1b06-2a86-40a9-950f-1c576ddcd35a:802-3-ethernet:eth0';
		Assert::equal($this->entity, Connection::nmCliDeserialize($string));
	}

	/**
	 * Tests the function to get network connection name
	 */
	public function testGetName(): void {
		Assert::same(self::NAME, $this->entity->getName());
	}

	/**
	 * Tests the function to get network connection UUID
	 */
	public function testGetUuid(): void {
		Assert::same($this->uuid, $this->entity->getUuid());
	}

	/**
	 * Tests the function to get network connection type
	 */
	public function testGetType(): void {
		Assert::same($this->type, $this->entity->getType());
	}

	/**
	 * Tests the function to get network interface name
	 */
	public function testGetInterfaceName(): void {
		Assert::same(self::INTERFACE, $this->entity->getInterfaceName());
	}

	/**
	 * Tests the function to serialize network connection entity into JSON
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'name' => self::NAME,
			'uuid' => $this->uuid->toString(),
			'type' => $this->type->toScalar(),
			'interfaceName' => self::INTERFACE,
		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

}

$test = new ConnectionTest();
$test->run();
