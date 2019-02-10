<?php
/**
 * TEST: App\GatewayModule\Models\UpdaterManager
 * @covers App\GatewayModule\Models\UpdaterManager
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\GatewayModule\Models;

use App\GatewayModule\Models\UpdaterManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for tool for updating packages of IQRF Gateways
 */
class UpdaterManagetTest extends CommandTestCase {

	/**
	 * @var UpdaterManager Tool for updating IQRF Gateway
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new UpdaterManager($this->commandManager);
	}

	/**
	 * Tests the function to list upgradable packages
	 */
	public function testListUpgradable(): void {
		$this->receiveAsyncCommand([$this, 'callback'],'apt-get --just-print upgrade', true);
		Assert::noError(function (): void {
			$this->manager->listUpgradable([$this, 'callback']);
		});
	}

	/**
	 * Tests the function to update list of packages
	 */
	public function testUpdate(): void {
		$this->receiveAsyncCommand([$this, 'callback'],'apt-get update', true);
		Assert::noError(function (): void {
			$this->manager->update([$this, 'callback']);
		});
	}

	/**
	 * Tests the function to upgrade packages
	 */
	public function testUpgrade(): void {
		$this->receiveAsyncCommand([$this, 'callback'],'apt-get upgrade -y', true);
		Assert::noError(function (): void {
			$this->manager->upgrade([$this, 'callback']);
		});
	}

	/**
	 * Just an empty callback
	 */
	public function callback(): void {
	}

}

$test = new UpdaterManagetTest();
$test->run();
