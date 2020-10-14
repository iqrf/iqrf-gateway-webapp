<?php

/**
 * TEST: App\NetworkModule\Entities\ConnectionDetail
 * @covers App\NetworkModule\Entities\ConnectionDetail
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Entities\WifiSecurity;

use App\NetworkModule\Entities\WifiSecurity\Leap;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for Cisco LEAP entity
 */
final class LeapTest extends TestCase {

	/**
	 * LEAP username
	 */
	private const USERNAME = 'name';

	/**
	 * LEAP password
	 */
	private const PASSWORD = 'pass';

	/**
	 * JSON serialized entity
	 */
	private const JSON = [
		'username' => self::USERNAME,
		'password' => self::PASSWORD,
	];

	/**
	 * @var Leap Cisco LEAP entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->entity = new Leap(self::USERNAME, self::PASSWORD);
	}

	/**
	 * Tests the function to deserialize the Cisco LEAP entity from JSON
	 */
	public function testJsonDeserialize(): void {
		Assert::equal($this->entity, Leap::jsonDeserialize((object) self::JSON));
	}

	/**
	 * Tests the function to serialize the Cisco LEAP entity into JSON
	 */
	public function testJsonSerialize(): void {
		Assert::same(self::JSON, $this->entity->jsonSerialize());
	}

	/**
	 * Tests the function to deserialize the Cisco LEAP entity from nmcli configuration
	 */
	public function testNmCliDeserialize(): void {
		$nmCli = '802-11-wireless-security.leap-password:pass' . PHP_EOL .
			'802-11-wireless-security.leap-username:name';
		Assert::equal($this->entity, Leap::nmCliDeserialize($nmCli));
	}

	/**
	 * Tests the function to serialize the Cisco LEAP entity into nmcli configuration
	 */
	public function testNmCliSerialize(): void {
		$expected = '802-11-wireless-security.leap-password "pass" 802-11-wireless-security.leap-username "name" ';
		Assert::same($expected, $this->entity->nmCliSerialize());
	}

}

$test = new LeapTest();
$test->run();
