<?php

/**
 * TEST: App\CloudModule\Model\AwsManager
 * @covers App\CloudModule\Model\AwsManager
 * @phpVersion >= 5.6
 * @testCase
 */
declare(strict_types=1);

namespace Test\ServiceModule\Model;

use App\CloudModule\Model\AwsManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class AwsManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var AwsManager Amazon AWS IoT manager
	 */
	private $manager;

	/**
	 * @var array Values from Inteliments InteliGlue form
	 */
	private $formValues = [
		'endpoint' => 'localhost',
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
		$this->manager = \Mockery::mock(AwsManager::class)->makePartial();
		$this->manager->shouldReceive('uploadCertsAndKey')->andReturn(null);
	}

	/**
	 * @test
	 * Test function to create MQTT interface
	 */
	public function testCreateMqttInterface() {
		$timestamp = (new \DateTime())->format(\DateTime::ISO8601);
		$values = ArrayHash::from($this->formValues);
		$mqtt = [
			'Name' => 'MqttMessagingAws',
			'Enabled' => true,
			'BrokerAddr' => 'ssl://localhost:8883',
			'ClientId' => 'IqrfDpaMessaging1',
			'Persistence' => 1,
			'Qos' => 0,
			'TopicRequest' => 'Iqrf/DpaRequest',
			'TopicResponse' => 'Iqrf/DpaResponse',
			'User' => '',
			'Password' => '',
			'EnabledSSL' => true,
			'KeepAliveInterval' => 20,
			'ConnectTimeout' => 5,
			'MinReconnect' => 1,
			'MaxReconnect' => 64,
			'TrustStore' => '/etc/iqrf-daemon/certs/' . $timestamp . '-aws-ca.crt',
			'KeyStore' => '/etc/iqrf-daemon/certs/' . $timestamp . '-aws.crt',
			'PrivateKey' => '/etc/iqrf-daemon/certs/' . $timestamp . '-aws.key',
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
			'Name' => 'BaseServiceForMQTTAws',
			'Messaging' => 'MqttMessagingAws',
			'Serializers' => ['JsonSerializer'],
			'Properties' => ['AsyncDpaMessage' => true],
		];
		$actual = $this->manager->createBaseService();
		Assert::same($mqtt['Serializers'], iterator_to_array($actual['Serializers']));
		Assert::same($mqtt['Properties'], iterator_to_array($actual['Properties']));
		unset($actual['Serializers'], $actual['Properties'], $mqtt['Serializers'], $mqtt['Properties']);
		Assert::same($mqtt, iterator_to_array($actual));
	}

	/**
	 * @test
	 * Test function to create paths for certificates
	 */
	public function testCreatePaths() {
		$timestamp = (new \DateTime())->format(\DateTime::ISO8601);
		$actual = $this->manager->createPaths();
		$paths = [
			'caCert' => '/etc/iqrf-daemon/certs/' . $timestamp . '-aws-ca.crt',
			'cert' => '/etc/iqrf-daemon/certs/' . $timestamp . '-aws.crt',
			'key' => '/etc/iqrf-daemon/certs/' . $timestamp . '-aws.key',
		];
		Assert::same($paths, $actual);
	}

}

$test = new AwsManagerTest($container);
$test->run();
