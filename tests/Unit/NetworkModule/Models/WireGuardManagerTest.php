<?php

/**
 * TEST: App\NetworkModule\Models\WireGuardManager
 * @covers App\NetworkModule\Models\WireGuardManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Models;

use App\Models\Database\Entities\WireGuardInterface;
use App\Models\Database\Entities\WireGuardInterfaceIpv4;
use App\Models\Database\Entities\WireGuardInterfaceIpv6;
use App\Models\Database\Entities\WireGuardPeer;
use App\Models\Database\Entities\WireGuardPeerAddress;
use App\Models\Database\EntityManager;
use App\NetworkModule\Entities\MultiAddress;
use App\NetworkModule\Enums\WireGuardIpStack;
use App\NetworkModule\Exceptions\WireGuardInvalidEndpointException;
use App\NetworkModule\Exceptions\WireGuardKeyErrorException;
use App\NetworkModule\Models\WireGuardManager;
use Darsyn\IP\Version\Multi;
use Iqrf\CommandExecutor\Tester\Traits\CommandExecutorTestCase;
use Iqrf\ServiceManager\IServiceManager;
use Mockery;
use Mockery\MockInterface;
use Nette\Utils\ArrayHash;
use ReflectionClass;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for WireGuard manager
 */
final class WireGuardManagerTest extends TestCase {

	use CommandExecutorTestCase;

	/**
	 * Commands to be executed during testing
	 */
	private const COMMANDS = [
		'privateKey' => 'wg genkey',
		'publicKey' => 'wg pubkey',
		'tunnelState' => 'wg show \'wg_iqrf_0\'',
		'deleteTunnel' => 'ip link delete dev \'wg_iqrf_0\'',
	];

	/**
	 * WireGuard keypair
	 */
	private const WG_KEYPAIR = [
		'privateKey' => 'uImD+hh1Dp6uWvWJ+eaYdc2oRloC3TYUpPUUCwfBi0I=',
		'publicKey' => 'M0cY2Pma3Yk0qRDUeJF6Kg5lkYWG1YE2KFHZOe9PDGM=',
	];

	/**
	 * @var WireGuardInterface WireGuard interface entity
	 */
	private WireGuardInterface $interfaceEntity;

	/**
	 * @var WireGuardPeer WireGuard peer entity
	 */
	private WireGuardPeer $peerEntity;

	/**
	 * @var EntityManager Entity manager
	 */
	private EntityManager $entityManager;

	/**
	 * @var WireGuardManager WireGuard manager
	 */
	private WireGuardManager $manager;

	/**
	 * @var MockInterface|IServiceManager Mocked service manager
	 */
	private MockInterface|IServiceManager $serviceManager;

	/**
	 * Tests the function to get the interface IP stack (IPv4 only).
	 */
	public function testGetInterfaceIpStackIpv4(): void {
		$this->interfaceEntity->ipv4 = new WireGuardInterfaceIpv4(
			new MultiAddress(Multi::factory('192.168.1.1'), 24),
			$this->interfaceEntity,
		);
		$this->interfaceEntity->ipv6 = null;
		Assert::same(WireGuardIpStack::IPV4, $this->manager->getInterfaceIpStack($this->interfaceEntity));
	}

	/**
	 * Tests the function to get the interface IP stack (IPv6 only).
	 */
	public function testGetInterfaceIpStackIpv6(): void {
		$this->interfaceEntity->ipv4 = null;
		$this->interfaceEntity->ipv6 = new WireGuardInterfaceIpv6(
			new MultiAddress(Multi::factory('::1'), 128),
			$this->interfaceEntity,
		);
		Assert::same(WireGuardIpStack::IPV6, $this->manager->getInterfaceIpStack($this->interfaceEntity));
	}

	/**
	 * Tests the function to get the interface IP stack (dual stack).
	 */
	public function testGetInterfaceIpStackDual(): void {
		$this->interfaceEntity->ipv4 = new WireGuardInterfaceIpv4(
			new MultiAddress(Multi::factory('192.168.1.1'), 24),
			$this->interfaceEntity,
		);
		$this->interfaceEntity->ipv6 = new WireGuardInterfaceIpv6(
			new MultiAddress(Multi::factory('::1'), 128),
			$this->interfaceEntity,
		);
		Assert::same(WireGuardIpStack::DUAL, $this->manager->getInterfaceIpStack($this->interfaceEntity));
	}

	/**
	 * Tests the function to create peer (requires database mock - method saves data to db)
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
	 */

	/**
	 * Tests the function to create peer address
	 */
	public function testCreatePeerAddress(): void {
		$testPeer = new WireGuardPeer('Z4Csw6v+89bcamtek9elXmuIEA+6PeB6CLnjNh4dJzI=', null, 25, 'example.org', 51280, $this->interfaceEntity);
		$addrs = [
			ArrayHash::from([
				'address' => '192.168.1.2',
				'prefix' => 24,
			], true),
		];
		$this->peerEntity->addresses->add(new WireGuardPeerAddress(new MultiAddress(Multi::factory('192.168.1.2'), 24), $this->peerEntity));
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
			$this->manager->validateEndpoint('nonexistenttestdomain.org');
		}, WireGuardInvalidEndpointException::class);
	}

	/**
	 * Tests the function to generate WireGuard keypair
	 */
	public function testGenerateKeys(): void {
		$manager = Mockery::mock(WireGuardManager::class, [$this->commandExecutor, $this->entityManager, $this->serviceManager])->makePartial();
		$manager->shouldReceive('generatePrivateKey')
			->andReturn(self::WG_KEYPAIR['privateKey']);
		$manager->shouldReceive('generatePublicKey')
			->with(self::WG_KEYPAIR['privateKey'])
			->andReturn(self::WG_KEYPAIR['publicKey']);
		Assert::same(self::WG_KEYPAIR, $manager->generateKeys());
	}

	/**
	 * Tests the function to generate WireGuard private key
	 */
	public function testGeneratePrivateKey(): void {
		$this->receiveCommand(
			command: self::COMMANDS['privateKey'],
			stdout: self::WG_KEYPAIR['privateKey'],
		);
		Assert::same(self::WG_KEYPAIR['privateKey'], $this->manager->generatePrivateKey());
	}

	/**
	 * Tests the function to generate WireGuard private key and throw exception
	 */
	public function testGeneratePrivateKeyException(): void {
		$this->receiveCommand(
			command: self::COMMANDS['privateKey'],
			stderr: 'Usage: wg genkey',
			exitCode: 1,
		);
		Assert::throws(function (): void {
			$this->manager->generatePrivateKey();
		}, WireGuardKeyErrorException::class);
	}

	/**
	 * Tests the function to generate WireGuard public key
	 */
	public function testGeneratePublicKey(): void {
		$this->receiveCommand(
			command: self::COMMANDS['publicKey'],
			needSudo: false,
			timeout: 60,
			input: self::WG_KEYPAIR['privateKey'],
			stdout: self::WG_KEYPAIR['publicKey'],
		);
		Assert::same(self::WG_KEYPAIR['publicKey'], $this->manager->generatePublicKey(self::WG_KEYPAIR['privateKey']));
	}

	/**
	 * Tests the function to generate WireGuard public key and throw exception
	 */
	public function testGeneratePublicKeyException(): void {
		$this->receiveCommand(
			command: self::COMMANDS['publicKey'],
			needSudo: false,
			timeout: 60,
			input: '',
			stderr: 'Usage: wg pubkey',
			exitCode: 1,
		);
		Assert::throws(function (): void {
			$this->manager->generatePublicKey('');
		}, WireGuardKeyErrorException::class);
	}

	/**
	 * Tests the function to get active tunnel state
	 */
	public function testGetTunnelState(): void {
		$this->receiveCommand(
			command: self::COMMANDS['tunnelState'],
			needSudo: true,
		);
		Assert::same('active', $this->manager->getTunnelState($this->interfaceEntity));
	}

	/**
	 * Tests the function to get inactive tunnel state
	 */
	public function testGetTunnelStateInactive(): void {
		$this->receiveCommand(
			command: self::COMMANDS['tunnelState'],
			needSudo: true,
			stderr: 'Unable to access interface: No such device',
			exitCode: 1,
		);
		Assert::same('inactive', $this->manager->getTunnelState($this->interfaceEntity));
	}

	/**
	 * Tests the function to check if tunnel state is active
	 */
	public function testIsTunnelActiveTrue(): void {
		$this->receiveCommand(
			command: self::COMMANDS['tunnelState'],
			needSudo: true,
		);
		Assert::true($this->manager->isTunnelActive($this->interfaceEntity));
	}

	/**
	 * Tests the function to check if tunnel state is active
	 */
	public function testIsTunnelActiveFalse(): void {
		$this->receiveCommand(
			command: self::COMMANDS['tunnelState'],
			needSudo: true,
			stderr: 'Unable to access interface: No such device',
			exitCode: 1,
		);
		Assert::false($this->manager->isTunnelActive($this->interfaceEntity));
	}

	/**
	 * Tests the function to delete tunnel successfully
	 */
	public function testDeleteTunnel(): void {
		$this->receiveCommand(
			command: self::COMMANDS['deleteTunnel'],
			needSudo: true,
		);
		Assert::true($this->manager->deleteTunnel($this->interfaceEntity));
	}

	/**
	 * Tests the function to delete tunnel unsuccessfully
	 */
	public function testDeleteTunnelFailed(): void {
		$this->receiveCommand(
			command: self::COMMANDS['deleteTunnel'],
			needSudo: true,
			stderr: 'Cannot find device "wg_iqrf_0"',
			exitCode: 1,
		);
		Assert::false($this->manager->deleteTunnel($this->interfaceEntity));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->setUpCommandExecutor();
		// WireGuard interface entity
		$this->interfaceEntity = new WireGuardInterface('wg0', 'CHmgTLdcdr33Nr/GblDjKufGqWWxmnGv7a50hN6hZ0c=', 51775);
		$interfaceReflection = new ReflectionClass($this->interfaceEntity);
		$idProperty = $interfaceReflection->getProperty('id');
		$idProperty->setValue($this->interfaceEntity, 0);
		// WireGuard peer entity
		$this->peerEntity = new WireGuardPeer('Z4Csw6v+89bcamtek9elXmuIEA+6PeB6CLnjNh4dJzI=', null, 25, 'example.org', 51280, $this->interfaceEntity);
		$this->entityManager = Mockery::mock(EntityManager::class);
		$this->entityManager->shouldReceive('getWireGuardInterfaceIpv4Repository');
		$this->entityManager->shouldReceive('getWireGuardInterfaceIpv6Repository');
		$this->entityManager->shouldReceive('getWireGuardInterfaceRepository');
		$this->entityManager->shouldReceive('getWireGuardPeerAddressRepository');
		$this->entityManager->shouldReceive('getWireGuardPeerRepository');
		$this->serviceManager = Mockery::mock(IServiceManager::class);
		$this->manager = new WireGuardManager($this->commandExecutor, $this->entityManager, $this->serviceManager);
	}

}

$test = new WireGuardManagerTest();
$test->run();
