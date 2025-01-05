<?php

/**
 * TEST: App\Models\Database\Entities\Mapping
 * @covers App\Models\Database\Entities\Mapping
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

use App\Models\Database\Entities\NetworkOperator;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for network operator database entity
 */
class NetworkOperatorTest extends TestCase {

	/**
	 * @var string Network operator name
	 */
	private const NAME = 'T-Mobile CZ';

	/**
	 * @var string Network operator APN
	 */
	private const APN = 'internet.t-mobile.cz';

	/**
	 * @var string Network operator username
	 */
	private const USERNAME = 'gprs';

	/**
	 * @var string Network operator password
	 */
	private const PASSWORD = 'gprs';

	/**
	 * @var NetworkOperator Network operator entity
	 */
	private NetworkOperator $operator;

	/**
	 * Tests the function to return network operator name
	 */
	public function testGetName(): void {
		Assert::same(self::NAME, $this->operator->getName());
	}

	/**
	 * Tests the function to set network operator name
	 */
	public function testSetName(): void {
		$expected = 'T-Mobile SK';
		$this->operator->setName($expected);
		Assert::same($expected, $this->operator->getName());
	}

	/**
	 * Tests the function to return network operator APN
	 */
	public function testGetApn(): void {
		Assert::same(self::APN, $this->operator->getApn());
	}

	/**
	 * Tests the function to set network operator APN
	 */
	public function testSetApn(): void {
		$expected = 'internet';
		$this->operator->setApn($expected);
		Assert::same($expected, $this->operator->getApn());
	}

	/**
	 * Tests the function to return network operator username
	 */
	public function testGetUsername(): void {
		Assert::same(self::USERNAME, $this->operator->getUsername());
	}

	/**
	 * Tests the function to set network operator username
	 */
	public function testSetUsername(): void {
		$expected = 'test';
		$this->operator->setUsername($expected);
		Assert::same($expected, $this->operator->getUsername());
		$this->operator->setUsername();
		Assert::null($this->operator->getUsername());
	}

	/**
	 * Tests the function to return network operator password
	 */
	public function testGetPassword(): void {
		Assert::same(self::PASSWORD, $this->operator->getPassword());
	}

	/**
	 * Tests the function to set network operator password
	 */
	public function testSetPassword(): void {
		$expected = 'test';
		$this->operator->setPassword($expected);
		Assert::same($expected, $this->operator->getPassword());
		$this->operator->setPassword();
		Assert::null($this->operator->getPassword());
	}

	/**
	 * Tests the function to serialize network operator entity to JSON
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'id' => null,
			'name' => self::NAME,
			'apn' => self::APN,
			'username' => self::USERNAME,
			'password' => self::PASSWORD,
		];
		Assert::same($expected, $this->operator->jsonSerialize());
		$this->operator->setUsername();
		$this->operator->setPassword();
		unset($expected['username']);
		unset($expected['password']);
		Assert::same($expected, $this->operator->jsonSerialize());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->operator = new NetworkOperator(self::NAME, self::APN, self::USERNAME, self::PASSWORD);
	}

}

$test = new NetworkOperatorTest();
$test->run();
