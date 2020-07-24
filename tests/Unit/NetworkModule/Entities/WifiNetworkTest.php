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
	protected function setUp(): void {
		$this->entity = new WifiNetwork($this->inUse, $this->bssid, $this->ssid, $this->mode, $this->channel, $this->rate, $this->signal, $this->security);
	}

	/**
	 * Tests the function to create a new WiFi network entity from nmcli
	 */
	public function testFromNmCli(): void {
		$actual = WifiNetwork::fromNmCli('*:1A\:E8\:29\:E5\:CB\:9A:WIFI MAGDA:Infra:56:405 Mbit/s:70:▂▄▆_:WPA2');
		Assert::equal($this->entity, $actual);
	}

	/**
	 * Tests the function to check if the network is used
	 */
	public function testIsUsed(): void {
		Assert::true($this->entity->isUsed());
	}

	/**
	 * Tests the function to get the network's BSSID
	 */
	public function testGetBssid(): void {
		Assert::same($this->bssid, $this->entity->getBssid());
	}

	/**
	 * Tests the function to get the network's SSID
	 */
	public function testGetSsid(): void {
		Assert::same($this->ssid, $this->entity->getSsid());
	}

	/**
	 * Tests the function to get the network's mode
	 */
	public function testGetMode(): void {
		Assert::equal($this->mode, $this->entity->getMode());
	}

	/**
	 * Tests the function to get the network's channel
	 */
	public function testGetChannel(): void {
		Assert::same($this->channel, $this->entity->getChannel());
	}

	/**
	 * Tests the function to get the network's speed rate
	 */
	public function testGetRate(): void {
		Assert::same($this->rate, $this->entity->getRate());
	}

	/**
	 * Tests the function to get the network's signal strength
	 */
	public function testGetSignal(): void {
		Assert::same($this->signal, $this->entity->getSignal());
	}

	/**
	 * Tests the function to get the network's security
	 */
	public function testGetSecurity(): void {
		Assert::equal($this->security, $this->entity->getSecurity());
	}

	/**
	 * Tests the function to get JSON serialized data
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'inUse' => true,
			'bssid' => '1A:E8:29:E5:CB:9A',
			'ssid' => 'WIFI MAGDA',
			'mode' => 'infrastructure',
			'channel' => 56,
			'rate' => '405 Mbit/s',
			'signal' => 70,
			'security' => 'WPA2-Personal',

		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

}

$test = new WifiNetworkTest();
$test->run();
