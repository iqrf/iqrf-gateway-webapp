<?php

/**
 * TEST: App\NetworkModule\WifiSecurity\Eap
 * @covers App\NetworkModule\WifiSecurity\Eap
 * @phpVersion >= 7.4
 * @testCase
 */
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

namespace Tests\Unit\NetworkModule\Entities\WifiSecurity;

use App\NetworkModule\Entities\WifiSecurity\Eap;
use App\NetworkModule\Enums\EapPhaseOneMethod;
use App\NetworkModule\Enums\EapPhaseTwoMethod;
use App\NetworkModule\Utils\NmCliConnection;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

final class EapTest extends TestCase {

	/**
	 * @var string EAP phase one authentication method
	 */
	private const PHASE_ONE = 'peap';

	/**
	 * @var string EAP phase two authentication method
	 */
	private const PHASE_TWO = 'mschapv2';

	/**
	 * @var string EAP anonymous identity
	 */
	private const ANONYMOUS_IDENTITY = 'testclient';

	/**
	 * @var string EAP CA certificate
	 */
	private const CERT = '/certs/ca.cert';

	/**
	 * @var string EAP identity string
	 */
	private const IDENTITY = 'testuser';

	/**
	 * @var string EAP password
	 */
	private const PASSWORD = 'testpass';

	/**
	 * @var Eap 802.1x entity
	 */
	private Eap $entity;

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
		$nmCli = NmCliConnection::decode($nmCli);
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
