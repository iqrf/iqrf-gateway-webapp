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

namespace App\ApiModule\Version0\Models;

use App\GatewayModule\Models\CertificateManager;
use App\GatewayModule\Models\InfoManager;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Ecdsa\Sha256 as EcdsaSha256;
use Lcobucci\JWT\Signer\Ecdsa\Sha384 as EcdsaSha384;
use Lcobucci\JWT\Signer\Ecdsa\Sha512 as EcdsaSha512;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256 as RsaSha256;
use Nette\Utils\Strings;
use SpomkyLabs\Pki\CryptoTypes\AlgorithmIdentifier\AlgorithmIdentifier;
use SpomkyLabs\Pki\CryptoTypes\AlgorithmIdentifier\Asymmetric\ECPublicKeyAlgorithmIdentifier;
use SpomkyLabs\Pki\CryptoTypes\Asymmetric\EC\ECPrivateKey;
use Throwable;

/**
 * JWT configurator
 */
class JwtConfigurator {

	/**
	 * Constructor
	 * @param CertificateManager $certificateManager TLS certificate manager
	 * @param InfoManager $infoManager Gateway information manager
	 */
	public function __construct(
		private readonly CertificateManager $certificateManager,
		private readonly InfoManager $infoManager,
	) {
	}

	/**
	 * Creates a JWT configuration
	 * @return Configuration JWT configuration
	 */
	public function create(): Configuration {
		try {
			$privateKey = $this->certificateManager->getPrivateKey()->toPEM();
			$publicKey = $this->certificateManager->getPublicKey()->toPEM();
			$parsedKey = $this->certificateManager->getParsedPrivateKey();
			switch ($parsedKey->algorithmIdentifier()->oid()) {
				case AlgorithmIdentifier::OID_RSA_ENCRYPTION:
					$signer = new RsaSha256();
					break;
				case AlgorithmIdentifier::OID_EC_PUBLIC_KEY:
					$ecKey = ECPrivateKey::fromPEM($privateKey);
					switch ($ecKey->namedCurve()) {
						case ECPublicKeyAlgorithmIdentifier::CURVE_PRIME256V1:
							$signer = new EcdsaSha256();
							break;
						case ECPublicKeyAlgorithmIdentifier::CURVE_SECP384R1:
							$signer = new EcdsaSha384();
							break;
						case ECPublicKeyAlgorithmIdentifier::CURVE_SECP521R1:
							$signer = new EcdsaSha512();
							break;
						default:
							return $this->createFallback();
					}
					break;
				default:
					return $this->createFallback();
			}
			return Configuration::forAsymmetricSigner(
				signer: $signer,
				signingKey: InMemory::plainText($privateKey->string()),
				verificationKey: InMemory::plainText($publicKey->string()),
			);
		} catch (Throwable) {
			return $this->createFallback();
		}
	}

	/**
	 * Creates a fallback JWT configuration with a symmetric key
	 * @return Configuration Fallback JWT configuration
	 */
	private function createFallback(): Configuration {
		$key = $this->infoManager->getId() ?? $this->infoManager->getHostname();
		$keyLength = strlen($key);
		if ($keyLength < 32) {
			$key = str_repeat($key, (int) ceil(32 / $keyLength));
		}
		$key = Strings::substring($key, 0, 32);
		return Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($key));
	}

}
