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
	private $key = '098a141333b044f3f08de9826f0c6c1bbd76407de73ed7eb95c8d2ae7cde52a6';

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$validTo = new DateTime('2020-01-01T00:00');
		$this->entity = new ApiKey($this->key, $this->description, $validTo);
	}

	/**
	 * Tests the function to return API key description
	 */
	public function testGetDescription(): void {
		Assert::same($this->description, $this->entity->getDescription());
	}

	/**
	 * Tests the function to return API key
	 */
	public function testGetKey(): void {
		Assert::same($this->key, $this->entity->getKey());
	}

	/**
	 * Tests the function to check if the API key is expired
	 */
	public function testIsExpired(): void {
		Assert::true($this->entity->isExpired());
		$entity = new ApiKey($this->key, $this->description, null);
		Assert::false($entity->isExpired());
		$entity = new ApiKey($this->key, $this->description, new DateTime('2050-01-01T00:00'));
		Assert::false($entity->isExpired());
	}

}

$test = new ApiKeyTest();
$test->run();
