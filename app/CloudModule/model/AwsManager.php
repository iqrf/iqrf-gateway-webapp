<?php

/**
 * Copyright 2017-2018 IQRF Tech s.r.o.
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
declare(strict_types=1);

namespace App\CloudModule\Model;

use App\ConfigModule\Model\GenericManager;
use App\Model\CertificateManager;
use GuzzleHttp\Client;
use Nette;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;

/**
 * Tool for managing Amazon AWS IoT
 */
class AwsManager {

	use Nette\SmartObject;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $configManager;

	/**
	 * @var CertificateManager manager for certificates
	 */
	private $certManager;

	/**
	 * @var string Path to certificates
	 */
	private $path = '/etc/iqrf-daemon/certs/';

	/**
	 * @var string MQTT interface name
	 */
	private $interfaceName = 'MqttMessagingAws';

	/**
	 * Constructor
	 * @param CertificateManager $certManager Manager for certificates
	 * @param GenericManager $configManager Generic config manager
	 */
	public function __construct(CertificateManager $certManager, Genericmanager $configManager) {
		$this->certManager = $certManager;
		$this->configManager = $configManager;
	}

	/**
	 * Create MQTT interface
	 * @param ArrayHash $values Values from form
	 * @return ArrayHash MQTT interface
	 */
	public function createMqttInterface(ArrayHash $values) {
		$paths = $this->createPaths();
		$this->downloadCaCertificate();
		$this->checkCertificate($values);
		$this->uploadCertsAndKey($values, $paths);
		$this->configManager->setComponent('iqrf::MqttMessaging');
		$this->configManager->setFileName($this->interfaceName);
		$interface = [
			'instance' => $this->interfaceName,
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
			'TrustStore' => $this->path . 'aws-ca.crt',
			'KeyStore' => $paths['cert'],
			'PrivateKey' => $paths['key'],
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false,
			'acceptAsyncMsg' => false,
		];
		$this->configManager->save(ArrayHash::from($interface));
	}

	/**
	 * Check a certificate and a private key
	 * @param ArrayHash $values Form values
	 */
	public function checkCertificate(ArrayHash $values) {
		$cert = $values['cert']->getContents();
		$pKey = $values['key']->getContents();
		if (!$this->certManager->checkPrivateKey($cert, $pKey)) {
			throw new InvalidPrivateKeyForCertificate();
		}
	}

	/**
	 * Create paths for root CA certificate, certificate and private key
	 * @return array Paths for root CA certificate, certificate and private key
	 */
	public function createPaths() {
		$timestamp = (new \DateTime())->format(\DateTime::ISO8601);
		$path = $this->path . $timestamp;
		$paths['cert'] = $path . '-aws.crt';
		$paths['key'] = $path . '-aws.key';
		return $paths;
	}

	/**
	 * Upload root CA certificate, certificate and private key
	 * @param ArrayHash $values Form values
	 * @param array $paths Paths for root CA certificate, certificate and private key
	 */
	public function uploadCertsAndKey(ArrayHash $values, array $paths) {
		$cert = $values['cert'];
		$key = $values['key'];
		if ($cert->isOk()) {
			$cert->move($paths['cert']);
		}
		if ($key->isOk()) {
			$key->move($paths['key']);
		}
	}

	/**
	 * Download root CA certificate
	 */
	public function downloadCaCertificate() {
		$client = new Client();
		$caCertUrl = 'https://www.symantec.com/content/en/us/enterprise/verisign/roots/VeriSign-Class%203-Public-Primary-Certification-Authority-G5.pem';
		$caCert = $client->request('GET', $caCertUrl)->getBody();
		FileSystem::write($this->path . 'aws-ca.crt', $caCert);
	}

}
