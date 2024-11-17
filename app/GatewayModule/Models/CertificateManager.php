<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

namespace App\GatewayModule\Models;

use AcmePhp\Ssl\Certificate;
use AcmePhp\Ssl\ParsedKey;
use AcmePhp\Ssl\Parser\CertificateParser;
use AcmePhp\Ssl\Parser\KeyParser;
use AcmePhp\Ssl\PrivateKey;
use AcmePhp\Ssl\PublicKey;
use App\CoreModule\Models\PrivilegedFileManager;
use App\GatewayModule\Exceptions\CertificateNotFoundException;
use App\GatewayModule\Exceptions\PrivateKeyNotFoundException;
use Nette\IOException;

/**
 * TLS certificate manager
 */
class CertificateManager {

	/**
	 * Constructor
	 * @param PrivilegedFileManager $fileManager Privileged file manager
	 */
	public function __construct(
		private readonly PrivilegedFileManager $fileManager,
	) {
	}

	/**
	 * Returns information about the certificate
	 * @return array{subject: string, issuer: string|null, subjectAlternativeNames: array<string>, validTo: string, expired: bool, selfSigned: bool} Information about the certificate
	 * @throws CertificateNotFoundException
	 */
	public function getInfo(): array {
		$certificateParser = new CertificateParser();
		$certificate = $certificateParser->parse($this->getCertificate());
		return [
			'subject' => $certificate->getSubject(),
			'issuer' => $certificate->getIssuer(),
			'subjectAlternativeNames' => $certificate->getSubjectAlternativeNames(),
			'validTo' => $certificate->getValidTo()->format('c'),
			'expired' => $certificate->isExpired(),
			'selfSigned' => $certificate->isSelfSigned(),
		];
	}

	/**
	 * Returns TLS certificate
	 * @return Certificate TLS certificate
	 * @throws CertificateNotFoundException
	 */
	public function getCertificate(): Certificate {
		try {
			$certificate = $this->fileManager->read('cert.pem');
			if ($certificate === '') {
				throw new CertificateNotFoundException();
			}
			return new Certificate($certificate);
		} catch (IOException) {
			throw new CertificateNotFoundException();
		}
	}

	/**
	 * Returns private key
	 * @return PrivateKey Private key
	 * @throws PrivateKeyNotFoundException
	 */
	public function getPrivateKey(): PrivateKey {
		try {
			$privateKey = $this->fileManager->read('privkey.pem');
			if ($privateKey === '') {
				throw new PrivateKeyNotFoundException();
			}
			return new PrivateKey($privateKey);
		} catch (IOException) {
			throw new PrivateKeyNotFoundException();
		}
	}

	/**
	 * Returns public key
	 * @return PublicKey Public key
	 * @throws CertificateNotFoundException
	 */
	public function getPublicKey(): PublicKey {
		return $this->getCertificate()->getPublicKey();
	}

	/**
	 * Returns parsed private key
	 * @return ParsedKey Parsed private key
	 * @throws PrivateKeyNotFoundException
	 */
	public function getParsedPrivateKey(): ParsedKey {
		$keyParser = new KeyParser();
		return $keyParser->parse($this->getPrivateKey());
	}

}
