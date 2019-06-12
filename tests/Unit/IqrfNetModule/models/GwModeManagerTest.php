<?php

/**
 * TEST: App\IqrfNetModule\Models\GwModeManager
 * @covers App\IqrfNetModule\Models\GwModeManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Models;

use App\IqrfNetModule\Exceptions as IqrfException;
use App\IqrfNetModule\Models\GwModeManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for IQRF Gateway Daemon's mode manager
 */
class GwModeManagerTest extends WebSocketTestCase {

	/**
	 * @var GwModeManager IQRF Gateway Daemon's mode manager
	 */
	private $manager;

	/**
	 * Tests the function to get current IQRF Gateway Daemon's mode
	 */
	public function testGet(): void {
		$request = [
			'mType' => 'mngDaemon_Mode',
			'data' => [
				'req' => ['operMode' => ''],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, [$this->manager, 'get']);
	}

	/**
	 * Tests the function to set IQRF Gateway Daemon's mode (invalid mode)
	 */
	public function testSetInvalid(): void {
		Assert::exception(function (): void {
			$this->manager->set('invalid');
		}, IqrfException\InvalidOperationModeException::class);
	}

	/**
	 * Tests the function to set IQRF Gateway Daemon's mode (valid mode)
	 */
	public function testSetValid(): void {
		$modes = ['forwarding', 'operational', 'service'];
		foreach ($modes as $mode) {
			$request = [
				'mType' => 'mngDaemon_Mode',
				'data' => [
					'req' => ['operMode' => $mode],
					'returnVerbose' => true,
				],
			];
			$this->assertRequest($request, function () use ($mode): void {
				$this->manager->set($mode);
			});
		}
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new GwModeManager($this->request, $this->wsClient);
	}

}

$test = new GwModeManagerTest();
$test->run();
