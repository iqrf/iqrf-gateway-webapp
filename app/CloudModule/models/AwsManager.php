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

use App\CloudModule\Exceptions\InvalidPrivateKeyForCertificateException;
use App\ConfigModule\Models\GenericManager;
use App\CoreModule\Models\CertificateManager;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Nette\SmartObject;
use Nette\Utils\FileSystem;
use Nette\Utils\JsonException;

/**
 * Tool for managing Amazon AWS IoT
 */
class AwsManager implements IManager {

	use SmartObject;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $configManager;

	/**
	 * @var CertificateManager manager for certificates
	 */
	private $certManager;

	/**
	 * @var string Path to the certificates
	 */
	private $certPath;

	/**
	 * @var Client HTTP(S) client
	 */
	private $client;

	/**
	 * @var string MQTT interface name
	 */
	private $interfaceName = 'MqttMessagingAws';

	/**
	 * Constructor
	 * @param string $certPath Path to the certificates
	 * @param CertificateManager $certManager Manager for certificates
	 * @param GenericManager $configManager Generic config manager
	 * @param Client $client HTTP(S) client
	 */
	public function __construct(string $certPath, CertificateManager $certManager, GenericManager $configManager, Client $client) {
		FileSystem::createDir($certPath);
		$this->certPath = realpath($certPath) . '/';
		$this->certManager = $certManager;
		$this->client = $client;
		$this->configManager = $configManager;
	}

	/**
	 * Creates a new MQTT interface
	 * @param mixed[] $values Values from form
	 * @throws GuzzleException
	 * @throws InvalidPrivateKeyForCertificateException
	 * @throws JsonException
	 */
	public function createMqttInterface(array $values): void {
		$paths = $this->createPaths();
		$this->downloadCaCertificate();
		$this->checkCertificate($values);
		$this->uploadCertsAndKey($values, $paths);
		$this->configManager->setComponent('iqrf::MqttMessaging');
		$this->configManager->setFileName('iqrf__MqttMessaging_Aws');
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
			'TrustStore' => $this->certPath . 'aws-ca.crt',
			'KeyStore' => $paths['cert'],
			'PrivateKey' => $paths['key'],
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false,
			'acceptAsyncMsg' => false,
		];
		$this->configManager->save($interface);
	}

	/**
	 * Creates paths for root CA certificate, certificate and private key
	 * @return string[] Paths for root CA certificate, certificate and private key
	 */
	public function createPaths(): array {
		$timestamp = (new DateTime())->format(DateTime::ISO8601);
		$path = $this->certPath . $timestamp;
		$paths = [];
		$paths['cert'] = $path . '-aws.crt';
		$paths['key'] = $path . '-aws.key';
		return $paths;
	}

	/**
	 * Downloads the root CA certificate
	 * @throws GuzzleException
	 */
	public function downloadCaCertificate(): void {
		$caCertUrl = 'https://www.amazontrust.com/repository/AmazonRootCA2.pem';
		$caCert = $this->client->request('GET', $caCertUrl)->getBody();
		FileSystem::write($this->certPath . 'aws-ca.crt', $caCert);
	}

	/**
	 * Checks a certificate and a private key
	 * @param mixed[] $values Form values
	 * @throws InvalidPrivateKeyForCertificateException
	 */
	public function checkCertificate(array $values): void {
		$cert = $values['cert']->getContents();
		$pKey = $values['key']->getContents();
		if (!$this->certManager->checkPrivateKey($cert, $pKey)) {
			throw new InvalidPrivateKeyForCertificateException();
		}
	}

	/**
	 * Uploads root CA certificate, certificate and private key
	 * @param mixed[] $values Form values
	 * @param string[] $paths Paths for root CA certificate, certificate and private key
	 */
	public function uploadCertsAndKey(array $values, array $paths): void {
		$cert = $values['cert'];
		$key = $values['key'];
		if ($cert->isOk()) {
			$cert->move($paths['cert']);
		}
		if ($key->isOk()) {
			$key->move($paths['key']);
		}
	}

}
