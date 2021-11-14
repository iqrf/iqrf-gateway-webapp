<?php

/**
 * TEST: App\NetworkModule\Models\WireguardManager
 * @covers App\NetworkModule\Models\WireguardManager
 * @phpVersion >= 7.3
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Models;

use App\Models\Database\Entities\WireguardInterface;
use App\Models\Database\Entities\WireguardPeer;
use App\Models\Database\Entities\WireguardPeerAddress;
use App\Models\Database\EntityManager;
use App\NetworkModule\Entities\MultiAddress;
use App\NetworkModule\Exceptions\WireguardInvalidEndpointException;
use App\NetworkModule\Exceptions\WireguardKeyErrorException;
use App\NetworkModule\Models\WireguardManager;
use App\ServiceModule\Models\ServiceManager;
use Darsyn\IP\Version\Multi;
use Mockery;
use Mockery\MockInterface;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tests\Stubs\CoreModule\Models\Command;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Wireguard manager
 */
final class WireguardManagerTest extends CommandTestCase {

	/**
	 * Commands to be executed during testing
	 */
	private const COMMANDS = [
		'privateKey' => 'umask 077 && wg genkey',
		'publicKey' => 'wg pubkey',
		'tunnelState' => 'wg show wg0',
		'deleteTunnel' => 'ip link delete dev wg0',
	];

	/**
	 * Wireguard keypair
	 */
	private const WG_KEYPAIR = [
		'privateKey' => 'uImD+hh1Dp6uWvWJ+eaYdc2oRloC3TYUpPUUCwfBi0I=',
		'publicKey' => 'M0cY2Pma3Yk0qRDUeJF6Kg5lkYWG1YE2KFHZOe9PDGM=',
	];

	/**
	 * @var WireguardInterface Wireguard interface entity
	 */
	private $interfaceEntity;

	/**
	 * @var WireguardPeer Wireguard peer entity
	 */
	private $peerEntity;

	/**
	 * @var EntityManager Entity manager
	 */
	private $entityManager;

	/**
	 * @var WireguardManager Wireguard manager
	 */
	private $manager;

	/**
	 * @var MockInterface|ServiceManager Mocked service manager
	 */
	private $serviceManager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->interfaceEntity = new WireguardInterface('wg0', 'CHmgTLdcdr33Nr/GblDjKufGqWWxmnGv7a50hN6hZ0c=', 51775);
		$this->peerEntity = new WireguardPeer('Z4Csw6v+89bcamtek9elXmuIEA+6PeB6CLnjNh4dJzI=', null, 25, 'example.org', 51280, $this->interfaceEntity);
		$this->entityManager = Mockery::mock(EntityManager::class);
		$this->entityManager->shouldReceive('getWireguardInterfaceIpv4Repository');
		$this->entityManager->shouldReceive('getWireguardInterfaceIpv6Repository');
		$this->entityManager->shouldReceive('getWireguardInterfaceRepository');
		$this->entityManager->shouldReceive('getWireguardPeerAddressRepository');
		$this->entityManager->shouldReceive('getWireguardPeerRepository');
		$this->serviceManager = Mockery::mock(ServiceManager::class);
		$this->manager = new WireguardManager($this->commandManager, $this->entityManager, $this->serviceManager);
	}

	/**
	 * Tests the function to create peer
	 */
	public function testCreatePeer(): void {
		$peerObject = ArrayHash::from([
			'publicKey' => 'Z4Csw6v+89bcamtek9elXmuIEA+6PeB6CLnjNh4dJzI=',
			'psk' => null,
			'keepalive' => 25,
			'endpoint' => 'example.org',
			'port' => 51280,
			'allowedIPs' => [],
		], true);
		$peerObject->allowedIPs->ipv4 = $peerObject->allowedIPs->ipv6 = [];
		Assert::equal($this->peerEntity, $this->manager->createPeer($peerObject, $this->interfaceEntity));
	}

	/**
	 * Tests the function to create peer address
	 */
	public function testCreatePeerAddress(): void {
		$testPeer = new WireguardPeer('Z4Csw6v+89bcamtek9elXmuIEA+6PeB6CLnjNh4dJzI=', null, 25, 'example.org', 51280, $this->interfaceEntity);
		$addrs = [
			ArrayHash::from([
				'address' => '192.168.1.2',
				'prefix' => 24,
			], true),
		];
		$this->peerEntity->addAddress(new WireguardPeerAddress(new MultiAddress(Multi::factory('192.168.1.2'), 24), $this->peerEntity));
		$this->manager->createPeerAddresses($addrs, $testPeer);
		Assert::equal($this->peerEntity, $testPeer);
	}

	/**
	 * Tests the function to validate endpoint
	 */
	public function testValidateEndpoint(): void {
		Assert::noError(function (): void {
			$this->manager->validateEndpoint('example.org');
		});
	}

	/**
	 * Tests the function to validate nonexistent endpoint
	 */
	public function testValidateEndpointInvalid(): void {
		Assert::exception(function (): void {
			$this->manager->validateEndpoint('nonexistentdomain.org');
		}, WireguardInvalidEndpointException::class);
	}

	/**
	 * Tests the function to generate wireguard keypair
	 */
	public function testGenerateKeys(): void {
		$manager = Mockery::mock(WireguardManager::class, [$this->commandManager, $this->entityManager, $this->serviceManager])->makePartial();
		$manager->shouldReceive('generatePrivateKey')
			->andReturn(self::WG_KEYPAIR['privateKey']);
		$manager->shouldReceive('generatePublicKey')
			->with(self::WG_KEYPAIR['privateKey'])
			->andReturn(self::WG_KEYPAIR['publicKey']);
		Assert::same(self::WG_KEYPAIR, $manager->generateKeys());
	}

	/**
	 * Tests the function to generate wireguard private key
	 */
	public function testGeneratePrivateKey(): void {
		$this->receiveCommand(self::COMMANDS['privateKey'], false, self::WG_KEYPAIR['privateKey'], '', 0);
		Assert::same(self::WG_KEYPAIR['privateKey'], $this->manager->generatePrivateKey());
	}

	/**
	 * Tests the function to generate wireguard private key and throw exception
	 */
	public function testGeneratePrivateKeyException(): void {
		$command = new Command(self::COMMANDS['privateKey'], '', 'Usage: wg genkey', 1);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['privateKey'], false])->andReturn($command);
		Assert::throws(function (): void {
			$manager = new WireguardManager($this->commandManager, $this->entityManager, $this->serviceManager);
			$manager->generatePrivateKey();
		}, WireguardKeyErrorException::class);
	}

	/**
	 * Tests the function to generate wireguard public key
	 */
	public function testGeneratePublicKey(): void {
		$command = new Command(self::COMMANDS['publicKey'], self::WG_KEYPAIR['publicKey'], '', 0);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['publicKey'], false, self::WG_KEYPAIR['privateKey']])->andReturn($command);
		Assert::same(self::WG_KEYPAIR['publicKey'], $this->manager->generatePublicKey(self::WG_KEYPAIR['privateKey']));
	}

	/**
	 * Tests the function to generate wireguard public key and throw exception
	 */
	public function testGeneratePublicKeyException(): void {
		$command = new Command(self::COMMANDS['publicKey'], '', 'Usage: wg pubkey', 1);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['publicKey'], false, ''])->andReturn($command);
		Assert::throws(function (): void {
			$manager = new WireguardManager($this->commandManager, $this->entityManager, $this->serviceManager);
			$manager->generatePublicKey('');
		}, WireguardKeyErrorException::class);
	}

	/**
	 * Tests the function to get active tunnel state
	 */
	public function testGetTunnelState(): void {
		$this->receiveCommand(self::COMMANDS['tunnelState'], true, '', '', 0);
		Assert::same('active', $this->manager->getTunnelState($this->interfaceEntity));
	}

	/**
	 * Tests the function to get inactive tunnel state
	 */
	public function testGetTunnelStateInactive(): void {
		$this->receiveCommand(self::COMMANDS['tunnelState'], true, '', 'Unable to access interface: No such device', 1);
		Assert::same('inactive', $this->manager->getTunnelState($this->interfaceEntity));
	}

	/**
	 * Tests the function to check if tunnel state is active
	 */
	public function testIsTunnelActiveTrue(): void {
		$this->receiveCommand(self::COMMANDS['tunnelState'], true, '', '', 0);
		Assert::true($this->manager->isTunnelActive($this->interfaceEntity));
	}

	/**
	 * Tests the function to check if tunnel state is active
	 */
	public function testIsTunnelActiveFalse(): void {
		$this->receiveCommand(self::COMMANDS['tunnelState'], true, '', 'Unable to access interface: No such device', 1);
		Assert::false($this->manager->isTunnelActive($this->interfaceEntity));
	}

	/**
	 * Tests the function to delete tunnel successfully
	 */
	public function testDeleteTunnel(): void {
		$this->receiveCommand(self::COMMANDS['deleteTunnel'], true, '', '', 0);
		Assert::true($this->manager->deleteTunnel($this->interfaceEntity));
	}

	/**
	 * Tests the function to delete tunnel unsuccessfully
	 */
	public function testDeleteTunnelFailed(): void {
		$this->receiveCommand(self::COMMANDS['deleteTunnel'], true, '', 'Cannot find device "wg0"', 1);
		Assert::false($this->manager->deleteTunnel($this->interfaceEntity));
	}

}

$test = new WireguardManagerTest();
$test->run();
