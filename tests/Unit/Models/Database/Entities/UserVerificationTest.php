<?php

/**
 * TEST: App\Models\Database\Entities\User
 * @covers App\Models\Database\Entities\User
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

use App\Models\Database\Entities\User;
use App\Models\Database\Entities\UserVerification;
use DateInterval;
use DateTime;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for user verification database entity
 */
final class UserVerificationTest extends TestCase {

	/**
	 * @var string Date time format
	 */
	private const DATETIME_FORMAT = 'Y-m-d H:i:00';

	/**
	 * @var UserVerification User verification entity
	 */
	private UserVerification $entity;

	/**
	 * @var User User entity
	 */
	private User $user;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->user = new User('admin', 'admin@iqrf.org', 'admin');
		$this->entity = new UserVerification($this->user);
		$this->entity->setCreatedAt();
	}


	/**
	 * Tests the function to get the user
	 */
	public function testGetUser(): void {
		Assert::equal($this->user, $this->entity->getUser());
	}

	/**
	 * Tests the function to get the created date
	 */
	public function testGetCreatedAt(): void {
		$now = new DateTime();
		$expected = $now->format(self::DATETIME_FORMAT);
		Assert::equal($expected, $this->entity->getCreatedAt()->format(self::DATETIME_FORMAT));
	}

	/**
	 * Tests the function to set the created date
	 */
	public function testSetCreatedAt(): void {
		$now = new DateTime();
		$this->entity->setCreatedAt($now);
		Assert::same($now, $this->entity->getCreatedAt());
	}

	/**
	 * Tests the function to check if the password recovery request is expired (valid)
	 */
	public function testIsExpiredFalse(): void {
		Assert::false($this->entity->isExpired());
	}

	/**
	 * Tests the function to check if the password recovery request is expired (expired)
	 */
	public function testIsExpiredTrue(): void {
		$now = new DateTime();
		$now->sub(new DateInterval('P1W'));
		$this->entity->setCreatedAt($now);
		Assert::true($this->entity->isExpired());
	}

}

$test = new UserVerificationTest();
$test->run();
