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
use Nette\Utils\Strings;
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
	 * API key description
	 */
	private const DESCRIPTION = 'Example API key';

	/**
	 * API key expiration date
	 */
	private const EXPIRATION = '2020-01-01T00:00:00+02:00';

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
		$this->expiration = new DateTime(self::EXPIRATION);
		$this->entity = new ApiKey(self::DESCRIPTION, $this->expiration);
		$this->key = $this->entity->getKey();
	}

	/**
	 * Tests the function to return API key description
	 */
	public function testGetDescription(): void {
		Assert::same(self::DESCRIPTION, $this->entity->getDescription());
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
		$key = Strings::substring($this->key, 23);
		Assert::true(password_verify($key, $this->entity->getHash()));
	}

	/**
	 * Tests the function to check if the API key is expired
	 */
	public function testIsExpired(): void {
		Assert::true($this->entity->isExpired());
		$entity = new ApiKey(self::DESCRIPTION, null);
		Assert::false($entity->isExpired());
		$entity = new ApiKey(self::DESCRIPTION, new DateTime('2050-01-01T00:00'));
		Assert::false($entity->isExpired());
	}

	/**
	 * Tests the function to verify API key
	 */
	public function testVerify(): void {
		Assert::true($this->entity->verify($this->key));
	}

	/**
	 * Tests the function to get JSON serialized entity
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'id' => null,
			'description' => self::DESCRIPTION,
			'expiration' => self::EXPIRATION,
		];
		$actual = $this->entity->jsonSerialize();
		Assert::match('~^[./A-Za-z0-9]{22}\.[A-Za-z0-9+/=]{44}$~', $actual['key']);
		unset($actual['key']);
		Assert::same($expected, $actual);
	}

}

$test = new ApiKeyTest();
$test->run();
