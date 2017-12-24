<?php

/**
 * TEST: App\CloudModule\Model\InteliGlueManager
 * @covers App\CloudModule\Model\InteliGlueManager
 * @phpVersion >= 5.6
 * @testCase
 */
declare(strict_types=1);

namespace Test\ServiceModule\Model;

use App\CloudModule\Model\InteliGlueManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class InteliGlueManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var InteliGlueManager Inteliments InteliGlue manager
	 */
	private $manager;

	/**
	 * @var array Values from Inteliments InteliGlue form 
	 */
	private $formValues = [
		'assignedPort' => 1234,
		'clientId' => 'client1234',
		'password' => 'pass1234',
		'rootTopic' => 'root',
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
		$this->manager = new InteliGlueManager();
	}

	/**
	 * @test
	 * Test function to create MQTT interface
	 */
	public function testCreateMqttInterface() {
		$values = ArrayHash::from($this->formValues);
		$mqtt = [
			'Name' => 'MqttMessagingInteliGlue',
			'Enabled' => true,
			'BrokerAddr' => 'ssl://mqtt.inteliglue.com:1234',
			'ClientId' => 'client1234',
			'Persistence' => 1,
			'Qos' => 0,
			'TopicRequest' => 'root/Iqrf/DpaRequest',
			'TopicResponse' => 'root/Iqrf/DpaResponse',
			'User' => 'client1234',
			'Password' => 'pass1234',
			'EnabledSSL' => true,
			'KeepAliveInterval' => 20,
			'ConnectTimeout' => 5,
			'MinReconnect' => 1,
			'MaxReconnect' => 64,
			'TrustStore' => '/etc/iqrf-daemon/certs/inteliments-ca.crt',
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
			'Name' => 'BaseServiceForMQTTInteliGlue',
			'Messaging' => 'MqttMessagingInteliGlue',
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

$test = new InteliGlueManagerTest($container);
$test->run();
