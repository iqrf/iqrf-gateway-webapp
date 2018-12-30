<?php

/**
 * TEST: App\IqrfNetModule\Models\MessageIdManager
 * @covers App\IqrfNetModule\Models\MessageIdManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\IqrfNetModule\Models;

use App\IqrfNetModule\Models\MessageIdManager;
use DateTime;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for message ID generator
 */
class MessageIdManagerTest extends TestCase {

	/**
	 * Test function to generate message ID
	 */
	public function testGenerate(): void {
		$expected = strval((new DateTime())->getTimestamp());
		$manager = new MessageIdManager();
		Assert::same($expected, $manager->generate());
	}

}


$test = new MessageIdManagerTest();
$test->run();
