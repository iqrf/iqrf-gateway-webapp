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

namespace App\ApiModule\Version0\Models;

use App\GatewayModule\Models\CertificateManager;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Ecdsa\Sha384 as EcdsaSha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256 as RsaSha256;
use Throwable;
use const OPENSSL_KEYTYPE_EC;
use const OPENSSL_KEYTYPE_RSA;

/**
 * JWT configurator
 */
class JwtConfigurator {

	/**
	 * @var CertificateManager TLS certificate manager
	 */
	private CertificateManager $certificateManager;

	/**
	 * Constructor
	 * @param CertificateManager $certificateManager TLS certificate manager
	 */
	public function __construct(CertificateManager $certificateManager) {
		$this->certificateManager = $certificateManager;
	}

	/**
	 * Creates a JWT configuration
	 * @return Configuration JWT configuration
	 */
	public function create(): Configuration {
		try {
			$privateKey = $this->certificateManager->getPrivateKey()->getPEM();
			$publicKey = $this->certificateManager->getPublicKey()->getPEM();
			if ($privateKey === '' || $publicKey === '') {
				return Configuration::forUnsecuredSigner();
			}
			$parsedKey = $this->certificateManager->getParsedPrivateKey();
			switch ($parsedKey->getType()) {
				case OPENSSL_KEYTYPE_RSA:
					$signer = new RsaSha256();
					break;
				case OPENSSL_KEYTYPE_EC:
					$signer = EcdsaSha256::create();
					break;
				default:
					return Configuration::forUnsecuredSigner();
			}
			return Configuration::forAsymmetricSigner($signer, InMemory::plainText($privateKey), InMemory::plainText($publicKey));
		} catch (Throwable $e) {
			return Configuration::forUnsecuredSigner();
		}
	}

}
