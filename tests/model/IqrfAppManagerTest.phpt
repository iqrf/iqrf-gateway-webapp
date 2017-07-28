<?php

/**
 * TEST: App\Model\IqrfAppManager
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\Model;

use App\Model\CommandManager;
use App\Model\IqrfAppManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

class IqrfAppManagerTest extends TestCase {

	private $container;

	function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * @test
	 * Test function to validation of raw IQRF packet
	 */
	public function testValidatePacket() {
		$commandManager = new CommandManager(false);
		$iqrfAppManager = new IqrfAppManager($commandManager);
		$validPackets = [
			'01.00.06.03.ff.ff', '01 00 06 03 ff ff',
			'01.00.06.03.ff.ff.', '01 00 06 03 ff ff.'
		];
		$invalidPackets = [
			';01.00.06.03.ff.ff', ';01 00 06 03 ff ff', '01.00.06.03.ff.ff;',
			'01 00 06 03 ff ff;', '; echo Test > test.log'
		];
		foreach ($validPackets as $packet) {
			Assert::true($iqrfAppManager->validatePacket($packet));
		}
		foreach ($invalidPackets as $packet) {
			Assert::false($iqrfAppManager->validatePacket($packet));
		}
	}

	/**
	 * @test
	 * Test function to change iqrf-daemon operation mode
	 */
	public function testChangeOperationMode() {
		$modesSuccess = ['forwarding', 'operational', 'service'];
		$outputSuccess = [
			'iqrfapp "{\"ctype\": \"conf\",\"type\": \"mode\",\"cmd\": \"forwarding\"}"',
			'iqrfapp "{\"ctype\": \"conf\",\"type\": \"mode\",\"cmd\": \"operational\"}"',
			'iqrfapp "{\"ctype\": \"conf\",\"type\": \"mode\",\"cmd\": \"service\"}"'
		];
		$commandManager = \Mockery::mock('App\Model\CommandManager');
		foreach ($outputSuccess as $output) {
			$commandManager->shouldReceive('send')->with($output, true)->andReturn(true);
		}
		$iqrfAppManager = new IqrfAppManager($commandManager);
		foreach ($modesSuccess as $mode) {
			Assert::true($iqrfAppManager->changeOperationMode($mode));
		}
		Assert::null($iqrfAppManager->changeOperationMode('invalid'));
	}

	/**
	 * @test
	 * Test function to send RAW IQRF packet
	 */
	public function testSendRaw() {
		$packet = '01.00.06.03.ff.ff';
		$expected = 'sudo iqrfapp raw ' . $packet;
		$commandManager = \Mockery::mock('App\Model\CommandManager');
		$commandManager->shouldReceive('send')->with('iqrfapp raw ' . $packet, true)->andReturn($expected);
		$iqrfAppManager = new IqrfAppManager($commandManager);
		Assert::equal($expected, $iqrfAppManager->sendRaw($packet));
	}

}

$test = new IqrfAppManagerTest($container);
$test->run();
