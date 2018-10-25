<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
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

namespace App\CloudModule\Models;

use App\ConfigModule\Models\GenericManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Nette\SmartObject;
use Nette\Utils\FileSystem;
use Nette\Utils\JsonException;

/**
 * Tool for managing IBM Bluemix
 */
class BluemixManager implements IManager {

	use SmartObject;

	/**
	 * @var string Path to the certificates
	 */
	private $certPath;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $configManager;

	/**
	 * @var Client HTTP(S) client
	 */
	private $client;

	/**
	 * @var string MQTT interface name
	 */
	private $interfaceName = 'MqttMessagingBluemix';

	/**
	 * Constructor
	 * @param string $certPath Path to the certificates
	 * @param GenericManager $configManager Generic config manager
	 * @param Client $client HTTP(S) client
	 */
	public function __construct(string $certPath, GenericManager $configManager, Client $client) {
		FileSystem::createDir($certPath);
		$this->certPath = realpath($certPath);
		$this->client = $client;
		$this->configManager = $configManager;
	}

	/**
	 * Create MQTT interface
	 * @param array $values Values from form
	 * @throws GuzzleException
	 * @throws JsonException
	 */
	public function createMqttInterface(array $values): void {
		$this->downloadCaCertificate();
		$this->configManager->setComponent('iqrf::MqttMessaging');
		$this->configManager->setFileName('iqrf__MqttMessaging_Bluemix');
		$interface = [
			'instance' => $this->interfaceName,
			'BrokerAddr' => 'ssl://' . $values['organizationId'] . '.messaging.internetofthings.ibmcloud.com:8883',
			'ClientId' => 'd:' . $values['organizationId'] . ':' . $values['deviceType'] . ':' . $values['deviceId'],
			'Persistence' => 1,
			'Qos' => 0,
			'TopicRequest' => 'iot-2/cmd/' . $values['eventId'] . '/fmt/json',
			'TopicResponse' => 'iot-2/evt/' . $values['eventId'] . '/fmt/json',
			'User' => 'use-token-auth',
			'Password' => $values['token'],
			'EnabledSSL' => true,
			'KeepAliveInterval' => 20,
			'ConnectTimeout' => 5,
			'MinReconnect' => 1,
			'MaxReconnect' => 64,
			'TrustStore' => $this->certPath . '/bluemix-ca.crt',
			'KeyStore' => '',
			'PrivateKey' => '',
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false,
			'acceptAsyncMsg' => false,
		];
		$this->configManager->save($interface);
	}

	/**
	 * Download root CA certificate
	 * @throws GuzzleException
	 */
	public function downloadCaCertificate(): void {
		$caCertUrl = 'https://raw.githubusercontent.com/ibm-watson-iot/iot-python/master/src/ibmiotf/messaging.pem';
		$caCert = $this->client->request('GET', $caCertUrl)->getBody();
		FileSystem::write($this->certPath . '/bluemix-ca.crt', $caCert);
	}

}
