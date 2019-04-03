<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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
 * Tool for managing TC Písek IoT platform
 */
class TcPisekManager implements IManager {

	use SmartObject;

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
	private $interfaceName = 'MqttMessagingTcPisek';

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
	 * Creates a new MQTT interface
	 * @param mixed[] $values Values from form
	 * @throws GuzzleException
	 * @throws JsonException
	 */
	public function createMqttInterface(array $values): void {
		$this->downloadCaCertificate();
		$this->configManager->setComponent('iqrf::MqttMessaging');
		$this->configManager->setFileName('iqrf__MqttMessaging_TcPisek');
		$interface = [
			'instance' => $this->interfaceName,
			'BrokerAddr' => 'ssl://' . $values['broker'] . ':8883',
			'ClientId' => 'IqrfDpaMessaging1',
			'Persistence' => 1,
			'Qos' => 0,
			'TopicRequest' => 'Iqrf/DpaRequest',
			'TopicResponse' => 'Iqrf/DpaResponse',
			'User' => $values['username'],
			'Password' => $values['password'],
			'EnabledSSL' => true,
			'KeepAliveInterval' => 20,
			'ConnectTimeout' => 5,
			'MinReconnect' => 1,
			'MaxReconnect' => 64,
			'TrustStore' => $this->certPath . '/tcPisek-ca.crt',
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
	 * Downloads the root CA certificate
	 * @throws GuzzleException
	 */
	public function downloadCaCertificate(): void {
		$caCertUrl = 'https://letsencrypt.org/certs/isrgrootx1.pem.txt';
		$caCert = $this->client->request('GET', $caCertUrl)->getBody();
		FileSystem::write($this->certPath . '/tcPisek-ca.crt', $caCert);
	}

}
