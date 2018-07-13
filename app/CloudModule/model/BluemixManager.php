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
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;

/**
 * Tool for managing IBM Bluemix
 */
class BluemixManager {

	use Nette\SmartObject;

	/**
	 * @var string Path to root CA certificate
	 */
	private $caPath = '/etc/iqrf-daemon/certs/bluemix-ca.crt';

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $configManager;

	/**
	 * @var string MQTT interface name
	 */
	private $interfaceName = 'MqttMessagingBluemix';

	/**
	 * Constructor
	 * @param GenericManager $configManager Generic config manager
	 */
	public function __construct(GenericManager $configManager) {
		$this->configManager = $configManager;
	}

	/**
	 * Create MQTT interface
	 * @param ArrayHash $values Values from form
	 * @return ArrayHash MQTT interface
	 */
	public function createMqttInterface(ArrayHash $values) {
		$this->downloadCaCertificate();
		$this->configManager->setComponent('iqrf::MqttMessaging');
		$this->configManager->setFileName($this->interfaceName);
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
			'TrustStore' => $this->caPath,
			'KeyStore' => '',
			'PrivateKey' => '',
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false,
			'acceptAsyncMsg' => false,
		];
		$this->configManager->save(ArrayHash::from($interface));
	}

	/**
	 * Download root CA certificate
	 */
	public function downloadCaCertificate() {
		$client = new Client();
		$caCertUrl = 'https://raw.githubusercontent.com/ibm-watson-iot/iot-python/master/src/ibmiotf/messaging.pem';
		$caCert = $client->request('GET', $caCertUrl)->getBody();
		FileSystem::write($this->caPath, $caCert);
	}

}
