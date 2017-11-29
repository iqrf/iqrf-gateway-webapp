<?php

/**
 * Copyright 2017 IQRF Tech s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\CloudModule\Model;

use Nette;
use Nette\Utils\ArrayHash;

/**
 * Tool for managing Amazon AWS IoT
 */
class AwsManager {

	use Nette\SmartObject;

	/**
	 * @var string Path to certificates
	 */
	private $path = '/etc/iqrf-daemon/certs/';

	/**
	 * @var string MQTT interface name
	 */
	private $interfaceName = 'MqttMessagingAws';

	/**
	 * Create MQTT interface
	 * @param ArrayHash $values Values from form
	 * @return ArrayHash MQTT interface
	 */
	public function createMqttInterface(ArrayHash $values) {
		$paths = $this->createPaths();
		$this->uploadCertsAndKey($values, $paths);
		$interface = [
			'Name' => $this->interfaceName,
			'Enabled' => true,
			'BrokerAddr' => 'ssl://' . $values['endpoint'] . ':8883',
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
			'TrustStore' => $paths['caCert'],
			'KeyStore' => $paths['cert'],
			'PrivateKey' => $paths['key'],
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false
		];
		return ArrayHash::from($interface);
	}

	/**
	 * Create base service
	 * @return ArrayHash Base service
	 */
	public function createBaseService() {
		$baseService = [
			'Name' => 'BaseServiceForMQTTAws',
			'Messaging' => $this->interfaceName,
			'Serializers' => ['JsonSerializer'],
			'Properties' => ['AsyncDpaMessage' => true],
		];
		return ArrayHash::from($baseService);
	}

	/**
	 * Create paths for root CA certificate, certificate and private key
	 * @return array Paths for root CA certificate, certificate and private key
	 */
	public function createPaths() {
		$timestamp = (new \DateTime())->format(\DateTime::ISO8601);
		$path = $this->path . $timestamp;
		$paths['caCert'] = $path . 'aws-ca.crt';
		$paths['cert'] = $path . '-aws.crt';
		$paths['key'] = $path . '-aws.key';
		return $paths;
	}

	/**
	 * Upload root CA certificate, certificate and private key
	 * @param ArrayHash $values FOrm values
	 * @param array $paths Paths for root CA certificate, certificate and private key
	 */
	public function uploadCertsAndKey(ArrayHash $values, array $paths) {
		$caCert = $values['caCert'];
		$cert = $values['cert'];
		$key = $values['key'];
		if ($caCert->isOk()) {
			$caCert->move($paths['caCert']);
		}
		if ($cert->isOk()) {
			$cert->move($paths['cert']);
		}
		if ($key->isOk()) {
			$key->move($paths['key']);
		}
	}

}
