<?php

/**
 * TEST: App\Model\IqrfAppManager
 * @phpVersion >= 5.6
 * @testCase
 */
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
		$iqrfAppManager = new IqrfAppManager(false);
		$packet0 = '01.00.06.03.ff.ff';
		$packet1 = '01 00 06 03 ff ff';
		$packet2 = ';01.00.06.03.ff.ff';
		$packet3 = ';01 00 06 03 ff ff';
		$packet4 = '01.00.06.03.ff.ff;';
		$packet5 = '01 00 06 03 ff ff;';
		$packet6 = '; echo Test > test.log';
		Assert::true($iqrfAppManager->validatePacket($packet0), 'Valid packet with dots.');
		Assert::true($iqrfAppManager->validatePacket($packet1), 'Valid packet with spaces.');
		Assert::false($iqrfAppManager->validatePacket($packet2), 'Invalid packet with dots.');
		Assert::false($iqrfAppManager->validatePacket($packet3), 'Invalid packet with spaces.');
		Assert::false($iqrfAppManager->validatePacket($packet4), 'Invalid packet with dots.');
		Assert::false($iqrfAppManager->validatePacket($packet5), 'Invalid packet with spaces.');
		Assert::false($iqrfAppManager->validatePacket($packet6), 'Invalid packet.');
	}

}

$test = new IqrfAppManagerTest($container);
$test->run();
