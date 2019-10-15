<?php
/**
 * TEST: App\IqrfNetModule\Models\StandardDaliManager
 * @covers App\IqrfNetModule\Models\StandardDaliManager
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Models;

use App\IqrfNetModule\Models\StandardDaliManager;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for IQRF Standard DALI manager
 */
class StandardDaliManagerTest extends WebSocketTestCase {

	/**
	 * @var StandardDaliManager IQRF Standard DALI manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new StandardDaliManager($this->request, $this->wsClient);
	}

	/**
	 * Tests the function to send DALI commands
	 */
	public function testSend(): void {
		$commands = [49409, 65521];
		$request = [
			'mType' => 'iqrfDali_SendCommands',
			'data' => [
				'req' => [
					'nAdr' => 4,
					'param' => [
						'commands' => $commands,
					],
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function () use ($commands): void {
			$this->manager->send(4, $commands);
		});
	}

}

$test = new StandardDaliManagerTest();
$test->run();
