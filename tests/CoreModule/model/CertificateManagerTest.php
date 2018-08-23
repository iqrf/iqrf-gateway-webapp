<?php

/**
 * TEST: App\CoreModule\Model\CertificateManager
 * @covers App\CoreModule\Model\CertificateManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\CoreModule\Model;

use App\CoreModule\Model\CertificateManager;
use Nette\DI\Container;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for certificate manager
 */
class CertificateManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var array Certificates
	 */
	private $certificates = [];

	/**
	 * @var array Private keys
	 */
	private $keys = [];

	/**
	 * @var CertificateManager Certificate manager
	 */
	private $manager;

	/**
	 * @var string Path to a directory with certificates and private keys
	 */
	private $path = __DIR__ . '/../../data/certificates/';

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp() {
		$this->manager = new CertificateManager();
		$this->certificates['ca0'] = FileSystem::read($this->path . 'ca0.pem');
		$this->certificates['ca1'] = FileSystem::read($this->path . 'ca1.pem');
		$this->certificates['intermediate0'] = FileSystem::read($this->path . 'intermediate0.pem');
		$this->certificates['0'] = FileSystem::read($this->path . 'cert0.pem');
		$this->certificates['1'] = FileSystem::read($this->path . 'cert1.pem');
		$this->keys['0'] = FileSystem::read($this->path . 'pkey0.key');
		$this->keys['1'] = FileSystem::read($this->path . 'pkey1.key');
	}

	/**
	 * Test function to check issuer of certificate (invalid issuer)
	 */
	public function testCheckIssuerInvalid() {
		Assert::false($this->manager->checkIssuer($this->certificates['ca1'], $this->certificates['intermediate0']));
	}

	/**
	 * Test function to check issuer of certificate (self-signed certificate)
	 */
	public function testCheckIssuerSelfSigned() {
		Assert::true($this->manager->checkIssuer($this->certificates['0'], $this->certificates['0']));
	}

	/**
	 * Test function to check issuer of certificate (CA signed certificate)
	 */
	public function testCheckIssuer() {
		Assert::true($this->manager->checkIssuer($this->certificates['ca0'], $this->certificates['intermediate0']));
	}

	/**
	 * Test function to check private key of certificate (fail)
	 */
	public function testCheckPrivateKeyFail() {
		Assert::false($this->manager->checkPrivateKey($this->certificates['1'], $this->keys['0']));
	}

	/**
	 * Test function to check private key of certificate (success)
	 */
	public function testCheckPrivateKeySuccess() {
		Assert::true($this->manager->checkPrivateKey($this->certificates['0'], $this->keys['0']));
	}

}

$test = new CertificateManagerTest($container);
$test->run();
