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

namespace App\Model;

use Nette;

/**
 * Tool for managing certificates.
 */
class CertificateManager {

	use Nette\SmartObject;

	/**
	 * Checks if a certificate coresponds to a CA certificate
	 * @param string $caCertificate CA certificate
	 * @param string $certificate Certificate
	 * @return bool
	 */
	public function checkIssuer(string $caCertificate, string $certificate): bool {
		$caCert = openssl_x509_parse($caCertificate);
		$cert = openssl_x509_parse($certificate);
		return $caCert['subject'] === $cert['issuer'];
	}

	/**
	 * Check if a private key corresponds to a certificate
	 * @param string $cert Certificate
	 * @param string $pKey Private key
	 * @param string $passphrase Passphrase for private key
	 * @return bool
	 */
	public function checkPrivateKey(string $cert, string $pKey, string $passphrase = ''): bool {
		$key = $this->getPrivateKey($pKey, $passphrase);
		return openssl_x509_check_private_key($cert, $key);
	}

	/**
	 * Gets private key
	 * @param string $key Private key
	 * @param string $passphrase Passphrase for private key
	 * @return string|false Parsed private key or false
	 */
	public function getPrivateKey(string $key, string $passphrase = '') {
		return openssl_pkey_get_private($key, $passphrase);
	}

}
