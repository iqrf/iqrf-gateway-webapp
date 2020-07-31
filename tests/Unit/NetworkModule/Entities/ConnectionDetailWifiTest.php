<?php


/**
 * TEST: App\NetworkModule\Entities\ConnectionDetail
 * @covers App\NetworkModule\Entities\ConnectionDetail
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Entities;

use App\NetworkModule\Entities\ConnectionDetail;
use App\NetworkModule\Entities\IPv4Connection;
use App\NetworkModule\Entities\IPv6Connection;
use App\NetworkModule\Entities\WifiConnection;
use App\NetworkModule\Entities\WifiConnectionSecurity;
use App\NetworkModule\Entities\WifiSecurity\Leap;
use App\NetworkModule\Entities\WifiSecurity\Wep;
use App\NetworkModule\Enums\ConnectionTypes;
use App\NetworkModule\Enums\IPv4Methods;
use App\NetworkModule\Enums\IPv6Methods;
use App\NetworkModule\Enums\WepKeyType;
use App\NetworkModule\Enums\WifiMode;
use App\NetworkModule\Enums\WifiSecurityType;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for network connection entity
 */
class ConnectionDetailWifiTest extends TestCase {

	/**
	 * NetworkManager data directory
	 */
	private const NM_DATA = __DIR__ . '/../../../data/networkManager/';

	/**
	 * Connection UUID
	 */
	private const UUID = '5c7010a8-88f6-48e6-8ab2-5ad713217831';

	/**
	 * @var string Network connection ID
	 */
	private $id = 'WIFI MAGDA';

	/**
	 * @var UuidInterface Network connection UUID
	 */
	private $uuid;

	/**
	 * @var ConnectionTypes Network connection type
	 */
	private $type;

	/**
	 * @var string Network interface name
	 */
	private $interfaceName = 'wlp4s0';

	/**
	 * @var IPv4Connection IPv4 network connection entity
	 */
	private $ipv4;

	/**
	 * @var IPv6Connection IPv6 network connection entity
	 */
	private $ipv6;

	/**
	 * @var WifiConnection WiFi network connection entity
	 */
	private $wifi;

	/**
	 * @var ConnectionDetail Network connection entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	public function __construct() {
		$this->uuid = Uuid::fromString(self::UUID);
		$this->type = ConnectionTypes::WIFI();
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->createIpv4Connection();
		$this->createIpv6Connection();
		$this->createWifiConnection();
		$this->entity = new ConnectionDetail($this->id, $this->uuid, $this->type, $this->interfaceName, $this->ipv4, $this->ipv6, $this->wifi);
	}

	/**
	 * Creates the IPv4 network connection entity
	 */
	private function createIpv4Connection(): void {
		$method = IPv4Methods::AUTO();
		$addresses = [];
		$gateway = null;
		$dns = [];
		$this->ipv4 = new IPv4Connection($method, $addresses, $gateway, $dns);
	}

	/**
	 * Creates the IPv6 network connection entity
	 */
	private function createIpv6Connection(): void {
		$method = IPv6Methods::AUTO();
		$addresses = [];
		$dns = [];
		$this->ipv6 = new IPv6Connection($method, $addresses, $dns);
	}

	/**
	 * Creates the WiFI network connection entity
	 */
	private function createWifiConnection(): void {
		$ssid = 'WIFI MAGDA';
		$mode = WifiMode::INFRA();
		$securityType = WifiSecurityType::WPA_PSK();
		$psk = 'password';
		$leap = new Leap('', '');
		$wep = new Wep(WepKeyType::UNKNOWN(), 0, ['', '', '', '']);
		$security = new WifiConnectionSecurity($securityType, $psk, $leap, $wep);
		$this->wifi = new WifiConnection($ssid, $mode, $security);
	}

	/**
	 * Tests the function to deserialize network connection entity from nmcli connection configuration
	 */
	public function testNmCliDeserialize(): void {
		$nmCli = FileSystem::read(self::NM_DATA . self::UUID . '.conf');
		Assert::equal($this->entity, ConnectionDetail::nmCliDeserialize($nmCli));
	}

	/**
	 * Tests the function to get the network connection UUID
	 */
	public function testGetUuid(): void {
		Assert::same($this->uuid, $this->entity->getUuid());
	}

	/**
	 * Tests the function to get the network connection type
	 */
	public function testGetType(): void {
		Assert::same($this->type, $this->entity->getType());
	}

	/**
	 * Tests the function to get the network interface name
	 */
	public function testGetInterfaceName(): void {
		Assert::same($this->interfaceName, $this->entity->getInterfaceName());
	}

	/**
	 * Tests the function to serialize network connection entity into JSON
	 */
	public function testJsonSerialize(): void {
		$json = FileSystem::read(self::NM_DATA . 'toForm/' . self::UUID . '.json');
		$expected = Json::decode($json, Json::FORCE_ARRAY);
		Assert::same($expected, $this->entity->jsonSerialize());
	}

}

$test = new ConnectionDetailWifiTest();
$test->run();
