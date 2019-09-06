<?php

/**
 * TEST: App\NetworkModule\Entities\Connection
 * @covers App\NetworkModule\Entities\Connection
 * @phpVersion >= 7.1
 * @testCase
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
class ConnectionTest extends TestCase {

	/**
	 * @var string Network connection name
	 */
	private $name = 'eth0';

	/**
	 * @var UuidInterface Network connection UUID
	 */
	private $uuid;

	/**
	 * @var ConnectionTypes Network connection type
	 */
	private $type;

	/**
	 * @var string Network interface name
	 */
	private $interfaceName = 'eth0';

	/**
	 * @var Connection Network connection entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	public function __construct() {
		$this->uuid = Uuid::fromString('25ab1b06-2a86-40a9-950f-1c576ddcd35a');
		$this->type = ConnectionTypes::ETHERNET();
		$this->entity = new Connection($this->name, $this->uuid, $this->type, $this->interfaceName);
	}

	/**
	 * Tests the function to create network connection entity from a string
	 */
	public function testFromString(): void {
		$string = 'eth0:25ab1b06-2a86-40a9-950f-1c576ddcd35a:802-3-ethernet:eth0';
		Assert::equal($this->entity, Connection::fromString($string));
	}

	/**
	 * Tests the function to get network connection name
	 */
	public function testGetName(): void {
		Assert::same($this->name, $this->entity->getName());
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
		Assert::same($this->interfaceName, $this->entity->getInterfaceName());
	}

}

$test = new ConnectionTest();
$test->run();
