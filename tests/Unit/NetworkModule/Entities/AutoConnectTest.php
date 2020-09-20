<?php

/**
 * TEST: App\NetworkModule\Entities\ConnectionDetail
 * @covers App\NetworkModule\Entities\ConnectionDetail
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Entities;

use App\NetworkModule\Entities\AutoConnect;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for automatic connecting entity
 */
final class AutoConnectTest extends TestCase {

	/**
	 * Is automatic connecting enabled?
	 */
	private const ENABLED = true;

	/**
	 * Connection priority
	 */
	private const PRIORITY = 0;

	/**
	 * Connection retries
	 */
	private const RETRIES = -1;

	/**
	 * JSON serialized entity
	 */
	private const JSON = [
		'enabled' => self::ENABLED,
		'priority' => self::PRIORITY,
		'retries' => self::RETRIES,
	];

	/**
	 * @var AutoConnect Automatic connecting entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->entity = new AutoConnect(self::ENABLED, self::PRIORITY, self::RETRIES);
	}

	/**
	 * Tests the function to deserialize the automatic connecting entity from JSON
	 */
	public function testJsonDeserialize(): void {
		Assert::equal($this->entity, AutoConnect::jsonDeserialize((object) self::JSON));
	}

	/**
	 * Tests the function to serialize the automatic connecting entity into JSON
	 */
	public function testJsonSerialize(): void {
		Assert::same(self::JSON, $this->entity->jsonSerialize());
	}

	/**
	 * Tests the function to deserialize the automatic connecting entity from nmcli configuration
	 */
	public function testNmCliDeserialize(): void {
		$nmCli = 'connection.autoconnect:yes' . PHP_EOL .
			'connection.autoconnect-priority:0' . PHP_EOL .
			'connection.autoconnect-retries:-1';
		Assert::equal($this->entity, AutoConnect::nmCliDeserialize($nmCli));
	}

	/**
	 * Tests the function to serialize the automatic connecting entity into nmcli configuration
	 */
	public function testNmCliSerialize(): void {
		$expected = 'connection.autoconnect "1" connection.autoconnect-priority "0" connection.autoconnect-retries "-1" ';
		Assert::same($expected, $this->entity->nmCliSerialize());
	}

}

$test = new AutoConnectTest();
$test->run();
