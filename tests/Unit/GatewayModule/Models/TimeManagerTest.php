<?php

/**
 * TEST: App\GatewayModule\Models\TimeManager
 * @covers App\GatewayModule\Models\TimeManager
 * @phpVersion >= 7.3
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\GatewayModule\Models;

use App\GatewayModule\Exceptions\NonexistentTimezoneException;
use App\GatewayModule\Models\TimeManager;
use Mockery;
use Tester\Assert;
use Tests\Stubs\CoreModule\Models\Command;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for time manager
 */
final class TimeManagerTest extends CommandTestCase {

	/**
	 * Commands to be executed
	 */
	private const COMMANDS = [
		'timestamp' => 'date +%s',
		'timezone' => 'timedatectl | grep "Time zone"',
		'listTimezones' => 'timedatectl list-timezones',
		'setTimezone' => 'timedatectl set-timezone Europe/London',
		'setTimezoneNonexistent' => 'timedatectl set-timezone Nonexistent/Nonexistent',
	];

	/**
	 * @var TimeManager Time manager
	 */
	private $manager;

	/**
	 * Sets up test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new TimeManager($this->commandManager);
	}

	/**
	 * Tests the function to get current time
	 */
	public function testCurrentTime(): void {
		$expected = [
			'time' => [
				'timestamp' => 1613756375,
				'name' => 'Europe/London',
				'code' => 'GMT',
				'offset' => '+0000',
			],
		];
		$timestampCommand = new Command(self::COMMANDS['timestamp'], '1613756375', '', 0);
		$timezone = 'Europe/London';
		$manager = Mockery::mock(TimeManager::class, [$this->commandManager])->makePartial();
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['timestamp']])
			->andReturn($timestampCommand);
		$manager->shouldReceive('getTimezone')
			->andReturn($timezone);
		Assert::same($expected, $manager->currentTime());
	}

	/**
	 * Tests the function to get time zone
	 */
	public function testGetTimezone(): void {
		$timezone = 'Time zone: Europe/London (GMT, +0000)';
		$command = new Command(self::COMMANDS['timezone'], $timezone, '', 0);
		$this->commandManager->shouldReceive('run')
			->withArgs(['timedatectl | grep "Time zone"'])
			->andReturn($command);
		$expected = 'Europe/London';
		Assert::same($expected, $this->manager->getTimezone());
	}

	/**
	 * Tests the function to retrieve available timezones
	 */
	public function testAvailableTimezones(): void {
		$timezones = 'Europe/London' . PHP_EOL . 'Europe/Prague';
		$command = new Command(self::COMMANDS['listTimezones'], $timezones, '', 0);
		$this->commandManager->shouldReceive('run')
			->andReturn($command);
		$expected = [
			[
				'name' => 'Europe/London',
				'code' => 'GMT',
				'offset' => '+0000',
			],
			[
				'name' => 'Europe/Prague',
				'code' => 'CET',
				'offset' => '+0100',
			],
		];
		Assert::same($expected, $this->manager->availableTimezones());
	}

	/**
	 * Tests the function to get timezone information
	 */
	public function testTimezoneInfo(): void {
		$expected = [
			'name' => 'Europe/London',
			'code' => 'GMT',
			'offset' => '+0000',
		];
		Assert::same($expected, $this->manager->timezoneInfo('Europe/London'));
	}

	/**
	 * Tests the function to set timezone
	 */
	public function testSetTimezone(): void {
		$command = new Command(self::COMMANDS['setTimezone'], '', '', 0);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['setTimezone'], true])
			->andReturn($command);
		Assert::noError(function (): void {
			$this->manager->setTimezone('Europe/London');
		});
	}

	/**
	 * Tests the function to set timezone with nonexistent timezone
	 */
	public function testSetTimezoneNonexistent(): void {
		$command = new Command(self::COMMANDS['setTimezoneNonexistent'], '', '', 1);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['setTimezoneNonexistent'], true])
			->andReturn($command);
		Assert::throws(function (): void {
			$this->manager->setTimezone('Nonexistent/Nonexistent');
		}, NonexistentTimezoneException::class);
	}

}

$test = new TimeManagerTest();
$test->run();
