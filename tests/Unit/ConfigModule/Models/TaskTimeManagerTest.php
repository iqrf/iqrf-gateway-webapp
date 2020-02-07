<?php

/**
 * TEST: App\ConfigModule\Models\TaskTimeManager
 * @covers App\ConfigModule\Models\TaskTimeManager
 * @phpVersion >= 7.1
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
class TaskTimeManagerTest extends TestCase {

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

	/**
	 * Tests the function to get the task's time specification (CRON)
	 */
	public function testGetTimeCron(): void {
		$expected = '0 0 0 * * * *';
		$config = (object) [
			'timeSpec' => (object) [
				'cronTime' => $expected,
				'exactTime' => false,
				'periodic' => false,
			],
		];
		Assert::same($expected, $this->manager->getTime($config));
	}

	/**
	 * Tests the function to get the task's time specification (one shot)
	 */
	public function testGetTimeOneShot(): void {
		$expected = 'one shot (2019-01-01T00:00:00)';
		$config = (object) [
			'timeSpec' => (object) [
				'exactTime' => true,
				'startTime' => '2019-01-01T00:00:00',
				'periodic' => false,
			],
		];
		Assert::same($expected, $this->manager->getTime($config));
	}

	/**
	 * Tests the function to get the task's time specification (period in seconds)
	 */
	public function testGetTimePeriodicSeconds(): void {
		$expected = 'every 30 seconds';
		$config = (object) [
			'timeSpec' => (object) [
				'exactTime' => false,
				'periodic' => true,
				'period' => 30,
			],
		];
		Assert::same($expected, $this->manager->getTime($config));
	}

	/**
	 * Tests the function to get the task's time specification (period in minutes)
	 */
	public function testGetTimePeriodicMinutes(): void {
		$expected = 'every 30:00 minutes';
		$config = (object) [
			'timeSpec' => (object) [
				'exactTime' => false,
				'periodic' => true,
				'period' => 1800,
			],
		];
		Assert::same($expected, $this->manager->getTime($config));
	}

	/**
	 * Tests the function to get the task's time specification (period in hours)
	 */
	public function testGetTimePeriodicHours(): void {
		$expected = 'every 12:00:00 hours';
		$config = (object) [
			'timeSpec' => (object) [
				'exactTime' => false,
				'periodic' => true,
				'period' => 12 * 3600,
			],
		];
		Assert::same($expected, $this->manager->getTime($config));
	}

}

$test = new TaskTimeManagerTest();
$test->run();
