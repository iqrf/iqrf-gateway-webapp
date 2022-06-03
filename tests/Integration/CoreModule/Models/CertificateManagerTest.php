<?php

/**
 * TEST: App\CoreModule\Models\CertificateManager
 * @covers App\CoreModule\Models\CertificateManager
 * @phpVersion >= 7.4
 * @testCase
 */
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

namespace Tests\Integration\CoreModule\Models;

use App\CoreModule\Models\CertificateManager;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for certificate manager
 */
final class CertificateManagerTest extends TestCase {

	/**
	 * @var array<string> Certificates
	 */
	private array $certificates = [];

	/**
	 * @var array<string> Private keys
	 */
	private array $keys = [];

	/**
	 * @var CertificateManager Certificate manager
	 */
	private CertificateManager $manager;

	/**
	 * @var string Path to a directory with certificates and private keys
	 */
	private const PATH = TESTER_DIR . '/data/certificates/';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->certificates['ca0'] = FileSystem::read(self::PATH . 'ca0.pem');
		$this->certificates['ca1'] = FileSystem::read(self::PATH . 'ca1.pem');
		$this->certificates['intermediate0'] = FileSystem::read(self::PATH . 'intermediate0.pem');
		$this->certificates['0'] = FileSystem::read(self::PATH . 'cert0.pem');
		$this->certificates['1'] = FileSystem::read(self::PATH . 'cert1.pem');
		$this->keys['0'] = FileSystem::read(self::PATH . 'pkey0.key');
		$this->keys['1'] = FileSystem::read(self::PATH . 'pkey1.key');
	}

	/**
	 * Tests the function to check the issuer of the certificate (the invalid issuer)
	 */
	public function testCheckIssuerInvalid(): void {
		Assert::false($this->manager->checkIssuer($this->certificates['ca1'], $this->certificates['intermediate0']));
	}

	/**
	 * Tests the function to check the issuer of the certificate (a self-signed certificate)
	 */
	public function testCheckIssuerSelfSigned(): void {
		Assert::true($this->manager->checkIssuer($this->certificates['0'], $this->certificates['0']));
	}

	/**
	 * Tests the function to check the issuer of the certificate (a CA signed certificate)
	 */
	public function testCheckIssuer(): void {
		Assert::true($this->manager->checkIssuer($this->certificates['ca0'], $this->certificates['intermediate0']));
	}

	/**
	 * Tests the function to check the private key of the certificate (fail)
	 */
	public function testCheckPrivateKeyFail(): void {
		Assert::false($this->manager->checkPrivateKey($this->certificates['1'], $this->keys['0']));
	}

	/**
	 * Tests the function to check the private key of the certificate (success)
	 */
	public function testCheckPrivateKeySuccess(): void {
		Assert::true($this->manager->checkPrivateKey($this->certificates['0'], $this->keys['0']));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->manager = new CertificateManager();
	}

}

$test = new CertificateManagerTest();
$test->run();
