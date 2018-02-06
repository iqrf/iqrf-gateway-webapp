<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
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
declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Utils\FileSystem;

/**
 * Tool for managing certificates.
 */
class CertificateManager {

	use Nette\SmartObject;

	/**
	 * Checks if a certificate coresponds to a CA certificate
	 * @param string $caCertPath Path to CA certificate
	 * @param string $certPath Path to certificate
	 * @return bool
	 */
	public function checkIssuer(string $caCertPath, string $certPath): bool {
		$caCert = openssl_x509_parse(FileSystem::read($caCertPath));
		$cert = openssl_x509_parse(FileSystem::read($certPath));
		return $caCert['subject'] === $cert['issuer'];
	}

	/**
	 * Check if a private key corresponds to a certificate
	 * @param string $certPath Path to certificate
	 * @param string $pKeyPath Path to private key
	 * @param string $passphrase Passphrase for private key
	 * @return bool
	 */
	public function checkPrivateKey(string $certPath, string $pKeyPath, string $passphrase = ''): bool {
		$cert = FileSystem::read($certPath);
		$pKey = $this->getPrivateKey($pKeyPath, $passphrase);
		return openssl_x509_check_private_key($cert, $pKey);
	}

	/**
	 * Gets private key
	 * @param string $path Path to private key
	 * @param string $passphrase Passphrase for private key
	 * @return type Parsed private key or false
	 */
	public function getPrivateKey(string $path, string $passphrase = '') {
		$key = FileSystem::read($path);
		return openssl_pkey_get_private($key, $passphrase);
	}

}
