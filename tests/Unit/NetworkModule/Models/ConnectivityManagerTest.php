<?php
/**
 * TEST: App\NetworkModule\Models\ConnectivityManager
 * @covers App\NetworkModule\Models\ConnectivityManager
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Models;

use App\NetworkModule\Enums\ConnectivityState;
use App\NetworkModule\Exceptions\NetworkManagerException;
use App\NetworkModule\Models\ConnectivityManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for network connectivity manager
 */
class ConnectivityManagerTest extends CommandTestCase {

	/**
	 * @var ConnectivityManager Network connectivity manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new ConnectivityManager($this->commandManager);
	}

	/**
	 * Tests the function to check network connectivity (failure)
	 */
	public function testCheckFailure(): void {
		$command = 'nmcli -t networking connectivity check';
		$this->receiveCommand($command, true, '', '', 10);
		Assert::throws(function (): void {
			$this->manager->check();
		}, NetworkManagerException::class);
	}

	/**
	 * Tests the function to check network connectivity (success)
	 */
	public function testCheckSuccess(): void {
		$command = 'nmcli -t networking connectivity check';
		$this->receiveCommand($command, true, 'full');
		Assert::equal(ConnectivityState::FULL(), $this->manager->check());
	}

}

$test = new ConnectivityManagerTest();
$test->run();
