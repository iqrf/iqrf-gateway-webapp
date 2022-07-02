<?php

/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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

namespace App\CoreModule\Models;

/**
 * Tool for managing certificates
 */
class CertificateManager {

	/**
	 * Checks if a certificate corresponds to a CA certificate
	 * @param string $caCertificate CA certificate
	 * @param string $certificate Certificate
	 * @return bool Is a certificate corresponds to a CA certificate?
	 */
	public function checkIssuer(string $caCertificate, string $certificate): bool {
		$caCert = openssl_x509_parse($caCertificate);
		$cert = openssl_x509_parse($certificate);
		return $caCert['subject'] === $cert['issuer'];
	}

	/**
	 * Checks if a private key corresponds to a certificate
	 * @param string $cert Certificate
	 * @param string $pKey Private key
	 * @param string $password Password for private key
	 * @return bool Is a private key corresponds to a certificate?
	 */
	public function checkPrivateKey(string $cert, string $pKey, string $password = ''): bool {
		$key = $this->getPrivateKey($pKey, $password);
		return openssl_x509_check_private_key($cert, $key);
	}

	/**
	 * Gets private key
	 * @param string $key Private key
	 * @param string $password Password for private key
	 * @return string|false Parsed private key or false
	 */
	public function getPrivateKey(string $key, string $password = '') {
		return openssl_pkey_get_private($key, $password);
	}

}
