<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
	 * @var Certificate|null TLS certificate
	 */
	private $certificate;

	/**
	 * @var CertificateParser TLS certificate parser
	 */
	private $certificateParser;

	/**
	 * @var PrivilegedFileManager Privileged file manager
	 */
	private $fileManager;

	/**
	 * @var KeyParser Private key parser
	 */
	private $keyParser;

	/**
	 * @var PrivateKey|null Private key
	 */
	private $privateKey;

	/**
	 * Constructor
	 * @param PrivilegedFileManager $fileManager Privileged file manager
	 */
	public function __construct(PrivilegedFileManager $fileManager) {
		$this->fileManager = $fileManager;
		$this->certificateParser = new CertificateParser();
		$this->keyParser = new KeyParser();
		try {
			$certificate = $this->fileManager->read('cert.pem');
			$this->certificate = $certificate === '' ? null : new Certificate($certificate);
		} catch (IOException $e) {
			$this->certificate = null;
		}
		try {
			$privateKey = $this->fileManager->read('privkey.pem');
			$this->privateKey = $privateKey === '' ? null : new PrivateKey($privateKey);
		} catch (IOException $e) {
			$this->privateKey = null;
		}
	}

	/**
	 * Returns information about the certificate
	 * @return array<string, array<string>|bool|string> Information about the certificate
	 * @throws CertificateNotFoundException
	 */
	public function getInfo(): array {
		$certificate = $this->certificateParser->parse($this->getCertificate());
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
		if ($this->certificate === null) {
			throw new CertificateNotFoundException();
		}
		return $this->certificate;
	}

	/**
	 * Returns private key
	 * @return PrivateKey Private key
	 * @throws PrivateKeyNotFoundException
	 */
	public function getPrivateKey(): PrivateKey {
		if ($this->privateKey === null) {
			throw new PrivateKeyNotFoundException();
		}
		return $this->privateKey;
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
		return $this->keyParser->parse($this->getPrivateKey());
	}

}
