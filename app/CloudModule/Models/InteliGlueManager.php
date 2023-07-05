<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

use App\CloudModule\Exceptions\CannotCreateCertificateDirectoryException;
use App\ConfigModule\Models\GenericManager;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\JsonException;

/**
 * Tool for managing Inteliments InteliGlue
 */
class InteliGlueManager implements IManager {

	/**
	 * @var string CA certificate filename
	 */
	private const CA_FILENAME = 'inteliments-ca.crt';

	/**
	 * Constructor
	 * @param string $certPath Path to the certificates
	 * @param GenericManager $configManager Generic config manager
	 * @param ClientInterface $client HTTP(S) client
	 */
	public function __construct(
		private string $certPath,
		private readonly GenericManager $configManager,
		private readonly ClientInterface $client,
	) {
	}

	/**
	 * Creates a new MQTT interface
	 * @param array<string, int|string> $values Values from form
	 * @throws GuzzleException
	 * @throws JsonException
	 */
	public function createMqttInterface(array $values): void {
		$this->createDirectory();
		$this->downloadCaCertificate();
		$this->configManager->setComponent('iqrf::MqttMessaging');
		$interface = [
			'instance' => 'MqttMessagingInteliGlue',
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
			'TrustStore' => $this->certPath . self::CA_FILENAME,
			'KeyStore' => '',
			'PrivateKey' => '',
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false,
			'acceptAsyncMsg' => false,
		];
		$this->configManager->save($interface, 'iqrf__MqttMessaging_InteliGlue');
	}

	/**
	 * Creates a directory for certificates
	 * @throws CannotCreateCertificateDirectoryException
	 */
	private function createDirectory(): void {
		try {
			FileSystem::createDir($this->certPath);
		} catch (IOException $e) {
			throw new CannotCreateCertificateDirectoryException();
		}
		$realPath = realpath($this->certPath);
		$this->certPath = (($realPath === false) ? $this->certPath : $realPath) . '/';
	}

	/**
	 * Downloads the root CA certificate
	 * @throws GuzzleException
	 * @throws IOException
	 */
	public function downloadCaCertificate(): void {
		$caCertUrl = 'https://inteliments.com/static/docs/inteliglue/downloads/DST_Root_CA_X3.pem.txt';
		$caCert = $this->client->request('GET', $caCertUrl)->getBody();
		FileSystem::write($this->certPath . self::CA_FILENAME, $caCert->getContents());
	}

}
