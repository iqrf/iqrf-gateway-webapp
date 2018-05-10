<?php

/**
 * TEST: App\Model\CertificateManager
 * @covers App\Model\CertificateManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types=1);

namespace Test\Model;

use App\Model\CertificateManager;
use Nette\DI\Container;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

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
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Set up test environment
	 */
	public function setUp() {
		$this->manager = new CertificateManager();
		$this->certificates['ca0'] = FileSystem::read(__DIR__ . '/certs/ca0.pem');
		$this->certificates['ca1'] = FileSystem::read(__DIR__ . '/certs/ca1.pem');
		$this->certificates['intermediate0'] = FileSystem::read(__DIR__ . '/certs/intermediate0.pem');
		$this->certificates['0'] = FileSystem::read(__DIR__ . '/certs/cert0.pem');
		$this->certificates['1'] = FileSystem::read(__DIR__ . '/certs/cert1.pem');
		$this->keys['0'] = FileSystem::read(__DIR__ . '/certs/pkey0.key');
		$this->keys['1'] = FileSystem::read(__DIR__ . '/certs/pkey1.key');
	}

	/**
	 * @test
	 * Test function to check issuer of certificate
	 */
	public function testCheckIssuer() {
		Assert::true($this->manager->checkIssuer($this->certificates['ca0'], $this->certificates['intermediate0']));
		Assert::true($this->manager->checkIssuer($this->certificates['0'], $this->certificates['0']));
		Assert::false($this->manager->checkIssuer($this->certificates['ca1'], $this->certificates['intermediate0']));
	}

	/**
	 * @test
	 * Test function to check private key of certificate
	 */
	public function testCheckPrivateKey() {
		Assert::true($this->manager->checkPrivateKey($this->certificates['0'], $this->keys['0']));
		Assert::true($this->manager->checkPrivateKey($this->certificates['1'], $this->keys['1']));
		Assert::false($this->manager->checkPrivateKey($this->certificates['1'], $this->keys['0']));
	}

}

$test = new CertificateManagerTest($container);
$test->run();
