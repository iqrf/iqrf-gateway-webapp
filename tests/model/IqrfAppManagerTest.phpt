<?php

/**
 * TEST: App\Model\IqrfAppManager
 * @phpVersion >= 5.6
 * @testCase
 */
use App\Model\CommandManager;
use App\Model\IqrfAppManager;
use Mockery;
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
	 * Test function to parse OS read info
	 */
	public function testParseOsReadInfo() {
		$commandManager = new CommandManager(false);
		$iqrfAppManager = new IqrfAppManager($commandManager);
		$packet = '00.00.02.80.00.00.00.00.05.a4.00.81.38.24.79.08.00.28.00.f0';
		$array = $iqrfAppManager->parseOsReadInfo($packet);
		$expected = [
			'ModuleId' => '8100A405', 'OsVersion' => '3.08D',
			'TrType' => 'DCTR-72D', 'McuType' => 'PIC16F1938',
			'OsBuild' => '7908', 'Rssi' => '00',
			'SupplyVoltage' => '3.00', 'Flags' => '00',
			'SlotLimits' => 'f0',
		];
		Assert::equal($expected, $array);
	}

	/**
	 * @test
	 * Test function to send RAW IQRF packet
	 */
	public function testSendRaw() {
		$packet = '01.00.06.03.ff.ff';
		$expected = 'sudo iqrfapp raw ' . $packet;
		$commandManager = Mockery::mock('App\Model\CommandManager');
		$commandManager->shouldReceive('send')->with('iqrfapp raw ' . $packet, true)->andReturn($expected);
		$iqrfAppManager = new IqrfAppManager($commandManager);
		Assert::equal($expected, $iqrfAppManager->sendRaw($packet));
	}

}

$test = new IqrfAppManagerTest($container);
$test->run();
