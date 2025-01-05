<?php

/**
 * TEST: App\IqrfNetModule\Models\MessageIdManager
 * @covers App\IqrfNetModule\Models\MessageIdManager
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

namespace Tests\Unit\IqrfNetModule\Models;

use App\IqrfNetModule\Models\MessageIdManager;
use Ramsey\Uuid\Rfc4122\FieldsInterface;
use Ramsey\Uuid\Uuid;
use Tester\Assert;
use Tester\TestCase;
use function assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for message ID generator
 */
final class MessageIdManagerTest extends TestCase {

	/**
	 * Tests the function to generate a message ID
	 */
	public function testGenerate(): void {
		$manager = new MessageIdManager();
		$uuid = Uuid::fromString($manager->generate());
		$fields = $uuid->getFields();
		assert($fields instanceof FieldsInterface);
		Assert::same(4, $fields->getVersion());
	}

}


$test = new MessageIdManagerTest();
$test->run();
