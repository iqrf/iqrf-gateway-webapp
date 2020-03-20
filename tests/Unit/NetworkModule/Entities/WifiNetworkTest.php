<?php

/**
 * TEST: App\NetworkModule\Entities\WifiNetwork
 * @covers App\NetworkModule\Entities\WifiNetwork
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Entities;

use App\NetworkModule\Entities\WifiNetwork;
use App\NetworkModule\Enums\WifiMode;
use App\NetworkModule\Enums\WifiSecurity;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for network connection entity
 */
class WifiNetworkTest extends TestCase {

	/**
	 * @var bool Is in use?
	 */
	private $inUse = true;

	/**
	 * @var string BSSID (MAC address)
	 */
	private $bssid = '1A:E8:29:E5:CB:9A';

	/**
	 * @var string SSID
	 */
	private $ssid = 'WIFI MAGDA';

	/**
	 * @var WifiMode Mode
	 */
	private $mode;

	/**
	 * @var int Channel
	 */
	private $channel = 56;

	/**
	 * @var string Speed rate
	 */
	private $rate = '405 Mbit/s';

	/**
	 * @var int Signal strength
	 */
	private $signal = 70;

	/**
	 * @var WifiSecurity Security
	 */
	private $security;

	/**
	 * @var WifiNetwork WiFI network entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	public function __construct() {
		$this->mode = WifiMode::INFRA();
		$this->security = WifiSecurity::WPA2_PERSONAL();
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void  {
		$this->entity = new WifiNetwork($this->inUse, $this->bssid, $this->ssid, $this->mode, $this->channel, $this->rate, $this->signal, $this->security);
	}

	/**
	 * Tests the function to create a new WiFi network entity from nmcli
	 */
	public function testFromNmCli(): void {
		$actual = WifiNetwork::fromNmCli('*:1A\:E8\:29\:E5\:CB\:9A:WIFI MAGDA:Infra:56:405 Mbit/s:70:▂▄▆_:WPA2');
		Assert::equal($this->entity, $actual);
	}

}

$test = new WifiNetworkTest();
$test->run();
