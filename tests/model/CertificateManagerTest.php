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
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

class CertificateManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

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
	}

	/**
	 * @test
	 * Test function to check issuer of certificate
	 */
	public function testCheckIssuer() {
		Assert::true($this->manager->checkIssuer(__DIR__ . '/certs/ca0.pem', __DIR__ . '/certs/intermediate0.pem'));
		Assert::true($this->manager->checkIssuer(__DIR__ . '/certs/cert0.pem', __DIR__ . '/certs/cert0.pem'));
		Assert::false($this->manager->checkIssuer(__DIR__ . '/certs/ca1.pem', __DIR__ . '/certs/intermediate0.pem'));
	}

	/**
	 * @test
	 * Test function to check private key of certificate
	 */
	public function testCheckPrivateKey() {
		Assert::true($this->manager->checkPrivateKey(__DIR__ . '/certs/cert0.pem', __DIR__ . '/certs/pkey0.key'));
		Assert::true($this->manager->checkPrivateKey(__DIR__ . '/certs/cert1.pem', __DIR__ . '/certs/pkey1.key'));
		Assert::false($this->manager->checkPrivateKey(__DIR__ . '/certs/cert1.pem', __DIR__ . '/certs/pkey0.key'));
	}

}

$test = new CertificateManagerTest($container);
$test->run();
