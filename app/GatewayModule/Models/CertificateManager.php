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

namespace App\GatewayModule\Models;

use App\GatewayModule\Exceptions\CertificateNotFoundException;
use App\GatewayModule\Exceptions\PrivateKeyNotFoundException;
use DateTimeImmutable;
use Iqrf\FileManager\PrivilegedFileManager;
use Nette\IOException;
use SpomkyLabs\Pki\CryptoEncoding\PEM;
use SpomkyLabs\Pki\CryptoTypes\Asymmetric\PrivateKey;
use SpomkyLabs\Pki\CryptoTypes\Asymmetric\PrivateKeyInfo;
use SpomkyLabs\Pki\CryptoTypes\Asymmetric\PublicKeyInfo;
use SpomkyLabs\Pki\X509\Certificate\Certificate;
use SpomkyLabs\Pki\X509\GeneralName\GeneralName;

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
	 * @return array{
	 *     subject: string,
	 *     issuer: string|null,
	 *     subjectAlternativeNames: array<string>,
	 *     validTo: string,
	 *     expired: bool,
	 *     selfSigned: bool,
	 *  } Information about the certificate
	 * @throws CertificateNotFoundException
	 */
	public function getInfo(): array {
		$certificate = $this->getCertificate();
		$certificateInfo = $certificate->tbsCertificate();
		$san = [];
		if ($certificateInfo->extensions()->hasSubjectAlternativeName()) {
			$sanIterator = $certificateInfo->extensions()
				->subjectAlternativeName()->names()->getIterator();
			/** @var GeneralName $name */
			foreach ($sanIterator as $name) {
				$san[] = $name->string();
			}
		}
		$now = new DateTimeImmutable();
		$valid = $certificateInfo->validity()->notBefore()->dateTime() <= $now
			&& $certificateInfo->validity()->notAfter()->dateTime() >= $now;
		return [
			'subject' => $certificateInfo->subject()->firstValueOf('cn')->stringValue(),
			'issuer' => $certificateInfo->issuer()->firstValueOf('cn')->stringValue(),
			'subjectAlternativeNames' => $san,
			'validTo' => $certificateInfo->validity()->notAfter()->dateTime()->format('c'),
			'expired' => !$valid,
			'selfSigned' => $certificate->isSelfIssued(),
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
			return Certificate::fromPEM(PEM::fromString($certificate));
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
			return PrivateKey::fromPEM(PEM::fromString($privateKey));
		} catch (IOException) {
			throw new PrivateKeyNotFoundException();
		}
	}

	/**
	 * Returns public key
	 * @return PublicKeyInfo Public key
	 * @throws CertificateNotFoundException
	 */
	public function getPublicKey(): PublicKeyInfo {
		return $this->getCertificate()->tbsCertificate()->subjectPublicKeyInfo();
	}

	/**
	 * Returns parsed private key
	 * @return PrivateKeyInfo Parsed private key
	 * @throws PrivateKeyNotFoundException
	 */
	public function getParsedPrivateKey(): PrivateKeyInfo {
		return $this->getPrivateKey()->privateKeyInfo();
	}

}
