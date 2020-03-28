<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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
use App\CoreModule\Models\CommandManager;
use Nette\IOException;

/**
 * TLS certificate manager
 */
class CertificateManager {

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var Certificate|null TLS certificate
	 */
	private $certificate;

	/**
	 * @var CertificateParser TLS certificate parser
	 */
	private $certificateParser;

	/**
	 * @var KeyParser Private key parser
	 */
	private $keyParser;

	/**
	 * @var string Path to directory with certificate and private key
	 */
	private $path;

	/**
	 * @var PrivateKey|null Private key
	 */
	private $privateKey;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(CommandManager $commandManager) {
		$this->commandManager = $commandManager;
		$this->path = '/etc/iqrf-gateway-daemon/certs/core/';
		$certificate = $this->readFile('cert.pem');
		if ($certificate !== '') {
			$this->certificate = new Certificate($certificate);
		}
		$this->certificateParser = new CertificateParser();
		$privateKey = $this->readFile('privkey.pem');
		if ($privateKey !== '') {
			$this->privateKey = new PrivateKey($privateKey);
		}
		$this->keyParser = new KeyParser();
	}

	/**
	 * Returns information about the certificate
	 * @return array<string, mixed> Information about the certificate
	 */
	public function getInfo(): array {
		$certificate = $this->certificateParser->parse($this->certificate);
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
	 */
	public function getCertificate(): Certificate {
		return $this->certificate;
	}

	/**
	 * Returns private key
	 * @return PrivateKey Private key
	 */
	public function getPrivateKey(): PrivateKey {
		return $this->privateKey;
	}

	/**
	 * Returns public key
	 * @return PublicKey Public key
	 */
	public function getPublicKey(): PublicKey {
		return $this->certificate->getPublicKey();
	}

	/**
	 * Returns parsed private key
	 * @return ParsedKey Parsed private key
	 */
	public function getParsedPrivateKey(): ParsedKey {
		return $this->keyParser->parse($this->privateKey);
	}

	/**
	 * Reads the file
	 * @param string $fileName File name
	 * @return string File content
	 * @throws IOException
	 */
	private function readFile(string $fileName): string {
		$command = $this->commandManager->run('cat ' . $this->path . $fileName, true);
		if ($command->getExitCode() !== 0) {
			throw new IOException();
		}
		return $command->getStdout();
	}

}
