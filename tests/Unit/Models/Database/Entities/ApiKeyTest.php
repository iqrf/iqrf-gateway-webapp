<?php

/**
 * TEST: App\Models\Database\Entities\ApiKey
 * @covers App\Models\Database\Entities\ApiKey
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

namespace Tests\Unit\Models\Database\Entities;

use App\Exceptions\ApiKeyExpirationPassedException;
use App\Exceptions\ApiKeyInvalidExpirationException;
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
	 * API key description
	 */
	private const DESCRIPTION = 'Example API key';

	/**
	 * API key expiration date in past
	 */
	private const PAST_EXPIRATION = '2020-01-01T00:00:00+02:00';

	/**
	 * API key expiration date in future
	 */
	private const FUTURE_EXPIRATION = '2099-01-01T00:00:00+02:00';

	/**
	 * @var ApiKey API key entity
	 */
	private ApiKey $entity;

	/**
	 * @var string API key
	 */
	private string $key;

	/**
	 * @var DateTime API key expiration
	 */
	private DateTime $expiration;

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
		Assert::exception(function (): void {
			$this->entity->setExpiration(new DateTime(self::PAST_EXPIRATION));
		}, ApiKeyExpirationPassedException::class, 'Expiration date has already passed');
	}

	/**
	 * Tests the function to set API key expiration from string
	 */
	public function testSetExpirationFromString(): void {
		$expiration = '2030-01-01T00:00:00+02:00';
		$expected = new DateTime($expiration);
		$this->entity->setExpirationFromString($expiration);
		Assert::equal($expected, $this->entity->getExpiration());
		$this->entity->setExpirationFromString(null);
		Assert::null($this->entity->getExpiration());
		Assert::exception(function (): void {
			$this->entity->setExpirationFromString(self::PAST_EXPIRATION);
		}, ApiKeyExpirationPassedException::class, 'Expiration date has already passed');
		Assert::exception(function (): void {
			$this->entity->setExpirationFromString('Invalid expiration format');
		}, ApiKeyInvalidExpirationException::class, 'Invalid expiration date');
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
		Assert::false($this->entity->isExpired());
		$entity = new ApiKey(self::DESCRIPTION, null);
		Assert::false($entity->isExpired());
		$entity = new ApiKey(self::DESCRIPTION, new DateTime(self::PAST_EXPIRATION));
		Assert::true($entity->isExpired());
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
			'expiration' => self::FUTURE_EXPIRATION,
		];
		$actual = $this->entity->jsonSerialize();
		Assert::match('~^[./A-Za-z0-9]{22}\.[A-Za-z0-9+/=]{44}$~', $actual['key']);
		unset($actual['key']);
		Assert::same($expected, $actual);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->expiration = new DateTime(self::FUTURE_EXPIRATION);
		$this->entity = new ApiKey(self::DESCRIPTION, $this->expiration);
		$this->key = $this->entity->getKey();
	}

}

$test = new ApiKeyTest();
$test->run();
