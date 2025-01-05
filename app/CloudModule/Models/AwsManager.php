<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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
use App\CloudModule\Exceptions\InvalidPrivateKeyForCertificateException;
use App\ConfigModule\Models\GenericManager;
use DateTime;
use DateTimeInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\JsonException;

/**
 * Tool for managing Amazon AWS IoT
 */
class AwsManager implements IManager {

	/**
	 * @var string CA certificate filename
	 */
	private const CA_FILENAME = 'aws-ca.crt';

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private GenericManager $configManager;

	/**
	 * @var string Path to the certificates
	 */
	private string $certPath;

	/**
	 * @var ClientInterface HTTP(S) client
	 */
	private ClientInterface $client;

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
	 * @throws InvalidPrivateKeyForCertificateException
	 * @throws JsonException
	 */
	public function createMqttInterface(array $values): void {
		$this->createDirectory();
		$paths = $this->createPaths();
		$this->downloadCaCertificate();
		$cert = $values['certificate'];
		$pKey = $values['privateKey'];
		$this->checkCertificate($cert, $pKey);
		$this->uploadCertsAndKey($paths, $cert, $pKey);
		$this->configManager->setComponent('iqrf::MqttMessaging');
		$interface = [
			'instance' => 'MqttMessagingAws',
			'BrokerAddr' => 'ssl://' . $values['endpoint'] . ':8883',
			'ClientId' => 'IqrfDpaMessaging1',
			'Persistence' => 1,
			'Qos' => 0,
			'TopicRequest' => 'Iqrf/DpaRequest',
			'TopicResponse' => 'Iqrf/DpaResponse',
			'User' => '',
			'Password' => '',
			'KeepAliveInterval' => 20,
			'ConnectTimeout' => 5,
			'MinReconnect' => 1,
			'MaxReconnect' => 64,
			'TrustStore' => $this->certPath . self::CA_FILENAME,
			'KeyStore' => $paths['cert'],
			'PrivateKey' => $paths['key'],
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false,
			'acceptAsyncMsg' => false,
		];
		$this->configManager->save($interface, 'iqrf__MqttMessaging_Aws');
	}

	/**
	 * Create a directory for certificates
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
	 * Creates paths for root CA certificate, certificate and private key
	 * @return array{cert: string, key: string} Paths for root CA certificate, certificate and private key
	 */
	public function createPaths(): array {
		$timestamp = (new DateTime())->format(DateTimeInterface::ATOM);
		$path = $this->certPath . $timestamp;
		$paths = [];
		$paths['cert'] = $path . '-aws.crt';
		$paths['key'] = $path . '-aws.key';
		return $paths;
	}

	/**
	 * Downloads the root CA certificate
	 * @throws GuzzleException
	 * @throws IOException
	 */
	public function downloadCaCertificate(): void {
		$caCertUrl = 'https://www.amazontrust.com/repository/AmazonRootCA2.pem';
		$caCert = $this->client->request('GET', $caCertUrl)->getBody();
		FileSystem::write($this->certPath . self::CA_FILENAME, $caCert->getContents());
	}

	/**
	 * Checks a certificate and a private key
	 * @param string $certificate Certificate
	 * @param string $privateKey Private key
	 * @throws InvalidPrivateKeyForCertificateException
	 */
	public function checkCertificate(string $certificate, string $privateKey): void {
		$key = openssl_pkey_get_private($privateKey, '');
		if (!openssl_x509_check_private_key($certificate, $key)) {
			throw new InvalidPrivateKeyForCertificateException();
		}
	}

	/**
	 * Uploads certificate and private key
	 * @param array<string> $paths Paths for certificate and private key
	 * @param string $certificate Certificate
	 * @param string $privateKey Private key
	 */
	public function uploadCertsAndKey(array $paths, string $certificate, string $privateKey): void {
		FileSystem::write($paths['cert'], $certificate);
		FileSystem::write($paths['key'], $privateKey);
	}

}
