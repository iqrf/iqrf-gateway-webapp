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

namespace App\ConfigModule\Model;

use DateInterval;
use DateTime;
use Nette;
use Nette\Utils\ArrayHash;

class AzureManager {

	use Nette\SmartObject;

	/**
	 * Create MQTT interface from MS Azure IoT Hub Connection string
	 * @param string $connectionString MS Azure IoT Hub Connection string
	 * @return ArrayHash MQTT interface
	 */
	public function createMqttInterface($connectionString) {
		$data = $this->parseConnectionString($connectionString);
		$endpoint = $data['HostName'] . '/devices/' . $data['DeviceId'];
		$token = $this->generateSasToken($endpoint, $data['SharedAccessKey']);
		$interface = [
			'Name' => 'MqttMessagingAzure',
			'Enabled' => true,
			'BrokerAddr' => 'ssl://' . $data['HostName'] . ':8883',
			'ClientId' => $data['DeviceId'],
			'Persistence' => 1,
			'Qos' => 0,
			'TopicRequest' => 'devices/' . $data['DeviceId'] . '/messages/devicebound/#',
			'TopicResponse' => 'devices/' . $data['DeviceId'] . '/messages/events/',
			'User' => $data['HostName'] . '/' . $data['DeviceId'],
			'Password' => $token,
			'EnabledSSL' => true,
			'KeepAliveInterval' => 20,
			'ConnectTimeout' => 5,
			'MinReconnect' => 1,
			'MaxReconnect' => 64,
			'TrustStore' => '',
			'KeyStore' => '',
			'PrivateKey' => '',
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false
		];
		return ArrayHash::from($interface);
	}

	/**
	 * Generate shared access signature token
	 * @param string $resourceUri URI prefix (by segment) of the endpoints that can be accessed with this token, starting with host name of the IoT hub (no protocol).
	 * @param string $signingKey Signing key
	 * @param string $policyName The name of the shared access policy to which this token refers. Absent if the token refers to device-registry credentials.
	 * @param int $expiresInMins Expiration in minutes
	 * @return string MS Azure Shared access signature token
	 */
	public function generateSasToken($resourceUri, $signingKey, $policyName = null, $expiresInMins = 525600) {
		$now = new DateTime();
		$expires = new DateInterval('PT' . $expiresInMins . 'M');
		$ttl = $now->add($expires)->getTimestamp();
		$encodedResourceUri = urlencode($resourceUri);
		$toSign = $encodedResourceUri . "\n" . $ttl;
		$hmac = hash_hmac('sha256', $toSign, base64_decode($signingKey), true);
		$signature = urlencode(base64_encode($hmac));
		$token = 'SharedAccessSignature sr=' . $encodedResourceUri . '&sig='
				. $signature . '&se=' . $ttl;
		if (!empty($policyName)) {
			$token .= '$skn=' . $policyName;
		}
		return $token;
	}

	/**
	 * Parse Microsoft Azure IoT HUb connection string
	 * @param string $connectionString MS Azure IoT Hub Connection string
	 * @return array Values from the connection string
	 */
	public function parseConnectionString($connectionString) {
		$connectionString = trim($connectionString, " =\t\n\r\0\x0B");
		$data = [];
		foreach (explode(';', $connectionString) as $i) {
			$j = explode('=', $i);
			$data[$j[0]] = $j[1];
		}
		return $data;
	}

}
