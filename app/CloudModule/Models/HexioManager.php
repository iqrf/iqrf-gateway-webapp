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
use Nette\Utils\Strings;

/**
 * Tool for managing Hexio IoT platform
 */
class HexioManager implements IManager {

	/**
	 * @var string CA certificate file name
	 */
	private const CA_FILENAME = 'hexio-ca.crt';

	/**
	 * @var string Path to the certificates
	 */
	private string $certPath;

	/**
	 * @var ClientInterface HTTP(S) client
	 */
	private ClientInterface $client;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private GenericManager $configManager;

	/**
	 * Constructor
	 * @param string $certPath Path to the certificates
	 * @param GenericManager $configManager Generic config manager
	 * @param ClientInterface $client HTTP(S) client
	 */
	public function __construct(string $certPath, GenericManager $configManager, ClientInterface $client) {
		$this->certPath = $certPath;
		$this->client = $client;
		$this->configManager = $configManager;
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
			'instance' => 'MqttMessagingHexio',
			'BrokerAddr' => 'ssl://' . $values['broker'] . ':8883',
			'ClientId' => $values['clientId'],
			'Persistence' => 1,
			'Qos' => 1,
			'TopicRequest' => '{no-process}/' . Strings::replace($values['topicRequest'], '#^\{no\-process\}\/#', ''),
			'TopicResponse' => $values['topicResponse'],
			'User' => $values['username'],
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
		$this->configManager->save($interface, 'iqrf__MqttMessaging_Hexio');
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
		$caCertUrl = 'https://letsencrypt.org/certs/isrgrootx1.pem.txt';
		$caCert = $this->client->request('GET', $caCertUrl)->getBody();
		FileSystem::write($this->certPath . self::CA_FILENAME, $caCert->getContents());
	}

}
