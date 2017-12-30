<?php

/**
 * TEST: App\CloudModule\Model\BluemixManager
 * @covers App\CloudModule\Model\BluemixManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types=1);

namespace Test\ServiceModule\Model;

use App\CloudModule\Model\BluemixManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class BluemixManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var BluemixManager Amazon AWS IoT manager
	 */
	private $manager;

	/**
	 * @var array Values from Inteliments InteliGlue form
	 */
	private $formValues = [
		'deviceId' => 'gw00',
		'deviceType' => 'gateway',
		'eventId' => 'event1234',
		'organizationId' => 'org1234',
		'token' => 'token1234',
	];

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Set up test environment
	 */
	public function setUp() {
		$this->manager = \Mockery::mock(BluemixManager::class)->makePartial();
		$this->manager->shouldReceive('downloadCaCertificate')->andReturn(null);
	}

	/**
	 * @test
	 * Test function to create MQTT interface
	 */
	public function testCreateMqttInterface() {
		$values = ArrayHash::from($this->formValues);
		$mqtt = [
			'Name' => 'MqttMessagingBluemix',
			'Enabled' => true,
			'BrokerAddr' => 'ssl://org1234.messaging.internetofthings.ibmcloud.com:8883',
			'ClientId' => 'd:org1234:gateway:gw00',
			'Persistence' => 1,
			'Qos' => 0,
			'TopicRequest' => 'iot-2/cmd/event1234/fmt/json',
			'TopicResponse' => 'iot-2/evt/event1234/fmt/json',
			'User' => 'use-token-auth',
			'Password' => 'token1234',
			'EnabledSSL' => true,
			'KeepAliveInterval' => 20,
			'ConnectTimeout' => 5,
			'MinReconnect' => 1,
			'MaxReconnect' => 64,
			'TrustStore' => '/etc/iqrf-daemon/certs/bluemix-ca.crt',
			'KeyStore' => '',
			'PrivateKey' => '',
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false
		];
		Assert::same($mqtt, iterator_to_array($this->manager->createMqttInterface($values)));
	}

	/**
	 * @test
	 * Test function to create Base service
	 */
	public function testCreateBaseService() {
		$mqtt = [
			'Name' => 'BaseServiceForMQTTBluemix',
			'Messaging' => 'MqttMessagingBluemix',
			'Serializers' => ['JsonSerializer'],
			'Properties' => ['AsyncDpaMessage' => true],
		];
		$actual = $this->manager->createBaseService();
		Assert::same($mqtt['Serializers'], iterator_to_array($actual['Serializers']));
		Assert::same($mqtt['Properties'], iterator_to_array($actual['Properties']));
		unset($actual['Serializers'], $actual['Properties'], $mqtt['Serializers'], $mqtt['Properties']);
		Assert::same($mqtt, iterator_to_array($actual));
	}

}

$test = new BluemixManagerTest($container);
$test->run();
