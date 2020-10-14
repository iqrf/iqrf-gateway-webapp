<?php

/**
 * TEST: App\ConfigModule\Models\TaskTimeManager
 * @covers App\ConfigModule\Models\TaskTimeManager
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\ConfigModule\Models;

use App\ConfigModule\Models\TaskTimeManager;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for scheduler's task time specification manager
 */
final class TaskTimeManagerTest extends TestCase {

	/**
	 * @var TaskTimeManager Scheduler's task time specification manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->manager = new TaskTimeManager();
	}

	/**
	 * Tests the function to convert CRON alias to an array
	 */
	public function testCronToArrayAlias(): void {
		$config = (object) [
			'timeSpec' => (object) [
				'cronTime' => '@daily',
			],
		];
		$expected = (object) [
			'timeSpec' => (object) [
				'cronTime' => ['@daily', '', '', '', '', '', ''],
			],
		];
		$this->manager->cronToArray($config);
		Assert::equal($expected, $config);
	}

	/**
	 * Tests the function to convert an invalid CRON alias to an array
	 */
	public function testCronToArrayAliasInvalid(): void {
		$config = (object) [
			'timeSpec' => (object) [
				'cronTime' => '@invalid',
			],
		];
		$expected = (object) [
			'timeSpec' => (object) [
				'cronTime' => ['', '', '', '', '', '', ''],
			],
		];
		$this->manager->cronToArray($config);
		Assert::equal($expected, $config);
	}

	/**
	 * Tests the function to convert CRON without seconds and year section to an array
	 */
	public function testCronToArrayWithoutSecondsAndYear(): void {
		$config = (object) [
			'timeSpec' => (object) [
				'cronTime' => '0 0 * * *',
			],
		];
		$expected = (object) [
			'timeSpec' => (object) [
				'cronTime' => ['0', '0', '0', '*', '*', '*', '*'],
			],
		];
		$this->manager->cronToArray($config);
		Assert::equal($expected, $config);
	}

	/**
	 * Tests the function to convert CRON without year section to an array
	 */
	public function testCronToArrayWithoutYear(): void {
		$config = (object) [
			'timeSpec' => (object) [
				'cronTime' => '0 0 0 * * *',
			],
		];
		$expected = (object) [
			'timeSpec' => (object) [
				'cronTime' => ['0', '0', '0', '*', '*', '*', '*'],
			],
		];
		$this->manager->cronToArray($config);
		Assert::equal($expected, $config);
	}

	/**
	 * Tests the function to convert CRON without seconds section to an array
	 */
	public function testCronToArrayWithoutSeconds(): void {
		$config = (object) [
			'timeSpec' => (object) [
				'cronTime' => '0 0 * * * 2020',
			],
		];
		$expected = (object) [
			'timeSpec' => (object) [
				'cronTime' => ['0', '0', '0', '*', '*', '*', '2020'],
			],
		];
		$this->manager->cronToArray($config);
		Assert::equal($expected, $config);
	}

	/**
	 * Tests the function to convert CRON with question mark to an array
	 */
	public function testCronToArrayWithQuestionMark(): void {
		$config = (object) [
			'timeSpec' => (object) [
				'cronTime' => '0 0 12 ? * 1,2,3,4,5 *',
			],
		];
		$expected = (object) [
			'timeSpec' => (object) [
				'cronTime' => ['0', '0', '12', '*', '*', '1,2,3,4,5', '*'],
			],
		];
		$this->manager->cronToArray($config);
		Assert::equal($expected, $config);
	}

	/**
	 * Tests the function to convert an invalid CRON to an array
	 */
	public function testCronToArrayInvalid(): void {
		$config = (object) [
			'timeSpec' => (object) [
				'cronTime' => 'INVALID *',
			],
		];
		$expected = (object) [
			'timeSpec' => (object) [
				'cronTime' => ['', '', '', '', '', '', ''],
			],
		];
		$this->manager->cronToArray($config);
		Assert::equal($expected, $config);
	}

	/**
	 * Tests the function to convert CRON in an array to a string
	 */
	public function testCronToString(): void {
		$config = (object) [
			'timeSpec' => (object) [
				'cronTime' => ['0', '0', '0', '*', '*', '*', '*'],
			],
		];
		$expected = (object) [
			'timeSpec' => (object) [
				'cronTime' => '0 0 0 * * * *',
			],
		];
		$this->manager->cronToString($config);
		Assert::equal($expected, $config);
	}

}

$test = new TaskTimeManagerTest();
$test->run();
