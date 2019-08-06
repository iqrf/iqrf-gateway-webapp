<?php
/**
 * TEST: App\GatewayModule\Models\PixlaManager
 * @covers App\GatewayModule\Models\PixlaManager
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\GatewayModule\Models;

use App\GatewayModule\Enums\PixlaService;
use App\GatewayModule\Models\PixlaManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for PIXLA management system manager
 */
class PixlaManagerTest extends CommandTestCase {

	/**
	 * SystemD service name of PIXLA client
	 */
	private const SERVICE_NAME = 'gwman-client.service';

	/**
	 * @var PixlaManager PIXLA management system manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new PixlaManager($this->commandManager);
	}

	/**
	 * Tests the function to disable and stop PIXLA client service
	 */
	public function testDisableService(): void {
		$commands = [
			'systemctl stop ' . self::SERVICE_NAME,
			'systemctl disable ' . self::SERVICE_NAME,
		];
		foreach ($commands as $command) {
			$this->receiveCommand($command, true, '');
		}
		Assert::noError([$this->manager, 'disableService']);
	}

	/**
	 * Tests the function to enable and start PIXLA client service
	 */
	public function testEnableService(): void {
		$commands = [
			'systemctl enable ' . self::SERVICE_NAME,
			'systemctl start ' . self::SERVICE_NAME,
		];
		foreach ($commands as $command) {
			$this->receiveCommand($command, true, '');
		}
		Assert::noError([$this->manager, 'enableService']);
	}

	/**
	 * Tests the function to get status of PIXLA client service
	 */
	public function testGetServiceStatus(): void {
		$expected = PixlaService::ENABLED();
		$command = 'systemctl is-enabled ' . self::SERVICE_NAME;
		$this->receiveCommand($command, true, 'enabled');
		Assert::same($expected, $this->manager->getServiceStatus());
	}

	/**
	 * Tests the function to get PIXLA token
	 */
	public function testGetToken(): void {
		$token = 'pixla-token';
		$command = 'cat /etc/gwman/customer_id';
		$this->receiveCommand($command, true, $token);
		Assert::same($token, $this->manager->getToken());
	}

}

$test = new PixlaManagerTest();
$test->run();
