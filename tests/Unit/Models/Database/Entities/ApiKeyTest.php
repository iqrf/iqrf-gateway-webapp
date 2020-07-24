<?php

/**
 * TEST: App\Models\Database\Entities\ApiKey
 * @covers App\Models\Database\Entities\ApiKey
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\Models\Database\Entities;

use App\Models\Database\Entities\ApiKey;
use DateTime;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for API key database entity
 */
class ApiKeyTest extends TestCase {

	/**
	 * @var ApiKey API key entity
	 */
	private $entity;

	/**
	 * @var string API key description
	 */
	private $description = 'Example API key';

	/**
	 * @var string API key
	 */
	private $key;

	/**
	 * @var DateTime API key expiration
	 */
	private $expiration;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->expiration = new DateTime('2020-01-01T00:00');
		$this->entity = new ApiKey($this->description, $this->expiration);
		$this->key = $this->entity->getKey();
	}

	/**
	 * Tests the function to return API key description
	 */
	public function testGetDescription(): void {
		Assert::same($this->description, $this->entity->getDescription());
	}

	/**
	 * Tests the function to set API key description
	 */
	public function testSetDescription(): void {
		$expected = 'New description';
		$this->entity->setDescription($expected);
		Assert::same($expected, $this->entity->getDescription());
	}

	/**
	 * Tests the function to return API key expiration
	 */
	public function testGetExpiration(): void {
		Assert::same($this->expiration, $this->entity->getExpiration());
	}

	/**
	 * Tests the function to set API key expiration
	 */
	public function testSetExpiration(): void {
		$this->entity->setExpiration(null);
		Assert::null($this->entity->getExpiration());
	}

	/**
	 * Tests the function to return API key hash
	 */
	public function testGetHash(): void {
		Assert::true(password_verify($this->key, $this->entity->getHash()));
	}

	/**
	 * Tests the function to check if the API key is expired
	 */
	public function testIsExpired(): void {
		Assert::true($this->entity->isExpired());
		$entity = new ApiKey($this->description, null);
		Assert::false($entity->isExpired());
		$entity = new ApiKey($this->description, new DateTime('2050-01-01T00:00'));
		Assert::false($entity->isExpired());
	}

	/**
	 * Tests the function to get JSON serialized entity
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'id' => null,
			'description' => 'Example API key',
			'expiration' => '2020-01-01T00:00:00+02:00',
		];
		$actual = $this->entity->jsonSerialize();
		Assert::match('~^[0-9a-f]{64}$~', $actual['key']);
		unset($actual['key']);
		Assert::same($expected, $actual);
	}

}

$test = new ApiKeyTest();
$test->run();
