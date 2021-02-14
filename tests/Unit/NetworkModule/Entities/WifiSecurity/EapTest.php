<?php

/**
 * TEST: App\NetworkModule\WifiSecurity\Eap
 * @covers App\NetworkModule\WifiSecurity\Eap
 * @phpVersion >= 7.3
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Entities\WifiSecurity;

use App\NetworkModule\Entities\WifiSecurity\Eap;
use App\NetworkModule\Enums\EapPhaseOneMethod;
use App\NetworkModule\Enums\EapPhaseTwoMethod;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

final class EapTest extends TestCase {

	/**
	 * EAP phase one authonetication method
	 */
	private const PHASE_ONE = 'peap';

	/**
	 * EAP phase two authentication method
	 */
	private const PHASE_TWO = 'mschapv2';

	/**
	 * EAP anonymous identity
	 */
	private const ANONYMOUS_IDENTITY = 'testclient';

	/**
	 * EAP CA certificate
	 */
	private const CERT = '/certs/ca.cert';

	/**
	 * EAP identity string
	 */
	private const IDENTITY = 'testuser';

	/**
	 * EAP password
	 */
	private const PASSWORD = 'testpass';

	/**
	 * @var Eap 802.1x entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->entity = new Eap(EapPhaseOneMethod::fromScalar(self::PHASE_ONE), EapPhaseTwoMethod::fromScalar(self::PHASE_TWO), self::ANONYMOUS_IDENTITY, self::CERT, self::IDENTITY, self::PASSWORD);
	}

	/**
	 * Tests the function to deserialize EAP entity from JSON object
	 */
	public function testJsonDeserialize(): void {
		$arrayHash = ArrayHash::from([
			'phaseOne' => self::PHASE_ONE,
			'phaseTwo' => self::PHASE_TWO,
			'anonymousIdentity' => self::ANONYMOUS_IDENTITY,
			'cert' => self::CERT,
			'identity' => self::IDENTITY,
			'password' => self::PASSWORD,
		], true);
		$expected = Eap::jsonDeserialize($arrayHash);
		Assert::equal($expected, $this->entity);
	}

	/**
	 * Tests the function to serialize EAP entity into JSON
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'phaseOneMethod' => self::PHASE_ONE,
			'phaseTwoMethod' => self::PHASE_TWO,
			'anonymousIdentity' => self::ANONYMOUS_IDENTITY,
			'cert' => self::CERT,
			'identity' => self::IDENTITY,
			'password' => self::PASSWORD,
		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

	/**
	 * Tests the function to deserialize EAP entity from NMCLI configuration string
	 */
	public function testNmCliDeserialize(): void {
		$nmCli = '802-1x.eap:peap' . PHP_EOL .
			'802-1x.phase2-auth:mschapv2' . PHP_EOL .
			'802-1x.anonymous-identity:testclient' . PHP_EOL .
			'802-1x.ca-cert:/certs/ca.cert' . PHP_EOL .
			'802-1x.identity:testuser' . PHP_EOL .
			'802-1x.password:testpass';
		Assert::equal($this->entity, Eap::nmCliDeserialize($nmCli));
	}

	/**
	 * Tests the function to serialize EAP entity into NMCLI configfuration string
	 */
	public function testNmCliSerialize(): void {
		$expected = sprintf('802-1x.eap "%s" 802-1x.phase2-auth "%s" 802-1x.anonymous-identity "%s" 802-1x.ca-cert "%s" 802-1x.identity "%s" 802-1x.password "%s" ', self::PHASE_ONE, self::PHASE_TWO, self::ANONYMOUS_IDENTITY, self::CERT, self::IDENTITY, self::PASSWORD);
		Assert::same($expected, $this->entity->nmCliSerialize());
	}

}

$test = new EapTest();
$test->run();
