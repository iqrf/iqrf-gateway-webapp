<?php

/**
 * TEST: App\ConfigModule\Models\TaskTimeManager
 * @covers App\ConfigModule\Models\TaskTimeManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\CoreModule\Model;

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
		$config['timeSpec']['cronTime'] = '@daily';
		$expected['timeSpec']['cronTime'] = ['@daily', '', '', '', '', '', ''];
		$this->manager->cronToArray($config);
		Assert::same($expected, $config);
	}

	/**
	 * Tests the function to convert an invalid CRON alias to an array
	 */
	public function testCronToArrayAliasInvalid(): void {
		$config['timeSpec']['cronTime'] = '@invalid';
		$expected['timeSpec']['cronTime'] = ['', '', '', '', '', '', ''];
		$this->manager->cronToArray($config);
		Assert::same($expected, $config);
	}

	/**
	 * Tests the function to convert CRON without seconds and year section to an array
	 */
	public function testCronToArrayWithoutSecondsAndYear(): void {
		$config['timeSpec']['cronTime'] = '0 0 * * *';
		$expected['timeSpec']['cronTime'] = ['0', '0', '0', '*', '*', '*', '*'];
		$this->manager->cronToArray($config);
		Assert::same($expected, $config);
	}

	/**
	 * Tests the function to convert CRON without year section to an array
	 */
	public function testCronToArrayWithoutYear(): void {
		$config['timeSpec']['cronTime'] = '0 0 0 * * *';
		$expected['timeSpec']['cronTime'] = ['0', '0', '0', '*', '*', '*', '*'];
		$this->manager->cronToArray($config);
		Assert::same($expected, $config);
	}

	/**
	 * Tests the function to convert CRON without seconds section to an array
	 */
	public function testCronToArrayWithoutSeconds(): void {
		$config['timeSpec']['cronTime'] = '0 0 * * * 2020';
		$expected['timeSpec']['cronTime'] = ['0', '0', '0', '*', '*', '*', '2020'];
		$this->manager->cronToArray($config);
		Assert::same($expected, $config);
	}

	/**
	 * Tests the function to convert an invalid CRON to an array
	 */
	public function testCronToArrayInvalid(): void {
		$config['timeSpec']['cronTime'] = 'INVALID *';
		$expected['timeSpec']['cronTime'] = ['', '', '', '', '', '', ''];
		$this->manager->cronToArray($config);
		Assert::same($expected, $config);
	}

	/**
	 * Tests the function to convert CRON in an array to a string
	 */
	public function testCronToString(): void {
		$config['timeSpec']['cronTime'] = ['0', '0', '0', '*', '*', '*', '*'];
		$expected['timeSpec']['cronTime'] = '0 0 0 * * * *';
		$this->manager->cronToString($config);
		Assert::same($expected, $config);
	}

	/**
	 * Tests the function to get the task's time specification (CRON)
	 */
	public function testGetTimeCron(): void {
		$expected = '0 0 0 * * * *';
		$config = [
			'timeSpec' => [
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
		$config = [
			'timeSpec' => [
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
		$config = [
			'timeSpec' => [
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
		$config = [
			'timeSpec' => [
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
		$config = [
			'timeSpec' => [
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
