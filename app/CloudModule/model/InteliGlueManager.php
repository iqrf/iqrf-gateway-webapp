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

namespace App\CloudModule\Model;

use App\ConfigModule\Model\GenericManager;
use GuzzleHttp\Client;
use Nette;
use Nette\Utils\FileSystem;

/**
 * Tool for managing Inteliments InteliGlue
 */
class InteliGlueManager implements IManager {

	use Nette\SmartObject;

	/**
	 * @var string Path to the certificates
	 */
	private $certPath;

	/**
	 * @var Client HTTP(S) client
	 */
	private $client;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $configManager;

	/**
	 * @var string MQTT interface name
	 */
	private $interfaceName = 'MqttMessagingInteliGlue';

	/**
	 * Constructor
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
	 */
	public function createMqttInterface(array $values): void {
		$this->downloadCaCertificate();
		$this->configManager->setComponent('iqrf::MqttMessaging');
		$this->configManager->setFileName('iqrf__MqttMessaging_InteliGlue');
		$interface = [
			'instance' => $this->interfaceName,
			'BrokerAddr' => 'ssl://mqtt.inteliglue.com:' . $values['assignedPort'],
			'ClientId' => $values['clientId'],
			'Persistence' => 1,
			'Qos' => 0,
			'TopicRequest' => $values['rootTopic'] . '/Iqrf/DpaRequest',
			'TopicResponse' => $values['rootTopic'] . '/Iqrf/DpaResponse',
			'User' => $values['clientId'],
			'Password' => $values['password'],
			'EnabledSSL' => true,
			'KeepAliveInterval' => 20,
			'ConnectTimeout' => 5,
			'MinReconnect' => 1,
			'MaxReconnect' => 64,
			'TrustStore' => $this->certPath . '/inteliments-ca.crt',
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
	 */
	public function downloadCaCertificate(): void {
		$caCertUrl = 'https://inteliments.com/static/docs/inteliglue/downloads/DST_Root_CA_X3.pem.txt';
		$caCert = $this->client->request('GET', $caCertUrl)->getBody();
		FileSystem::write($this->certPath . '/inteliments-ca.crt', $caCert);
	}

}
