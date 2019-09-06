<?php

/**
 * TEST: App\IqrfNetModule\Models\MessageIdManager
 * @covers App\IqrfNetModule\Models\MessageIdManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Models;

use App\IqrfNetModule\Models\MessageIdManager;
use Ramsey\Uuid\Uuid;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for message ID generator
 */
class MessageIdManagerTest extends TestCase {

	/**
	 * Tests the function to generate a message ID
	 */
	public function testGenerate(): void {
		$manager = new MessageIdManager();
		Assert::same(4, Uuid::fromString($manager->generate())->getVersion());
	}

}


$test = new MessageIdManagerTest();
$test->run();
