<?php

/**
 * TEST: App\CoreModule\Models\CertificateManager
 * @covers App\CoreModule\Models\CertificateManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\Integration\CoreModule\Model;

use App\CoreModule\Models\CertificateManager;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for certificate manager
 */
class CertificateManagerTest extends TestCase {

	/**
	 * @var string[] Certificates
	 */
	private $certificates = [];

	/**
	 * @var string[] Private keys
	 */
	private $keys = [];

	/**
	 * @var CertificateManager Certificate manager
	 */
	private $manager;

	/**
	 * @var string Path to a directory with certificates and private keys
	 */
	private $path = __DIR__ . '/../../../data/certificates/';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->certificates['ca0'] = FileSystem::read($this->path . 'ca0.pem');
		$this->certificates['ca1'] = FileSystem::read($this->path . 'ca1.pem');
		$this->certificates['intermediate0'] = FileSystem::read($this->path . 'intermediate0.pem');
		$this->certificates['0'] = FileSystem::read($this->path . 'cert0.pem');
		$this->certificates['1'] = FileSystem::read($this->path . 'cert1.pem');
		$this->keys['0'] = FileSystem::read($this->path . 'pkey0.key');
		$this->keys['1'] = FileSystem::read($this->path . 'pkey1.key');
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
