<?php
/**
 * TEST: App\IqrfNetModule\Models\SecurityManager
 * @covers App\IqrfNetModule\Models\SecurityManager
 * @phpVersion >= 7.2
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Models;

use App\IqrfNetModule\Enums\DataFormat;
use App\IqrfNetModule\Models\SecurityManager;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Test for IQMESH Security manager
 */
final class SecurityManagerTest extends WebSocketTestCase {

	/**
	 * Network device address
	 */
	private const ADDRESS = 0;

	/**
	 * @var SecurityManager IQMESH Security manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new SecurityManager($this->request, $this->wsClient);
	}

	/**
	 * Tests the function to set an access password in HEX format (max length)
	 */
	public function testSetAccessPasswordHexMax(): void {
		$request = [
			'mType' => 'iqrfEmbedOs_SetSecurity',
			'data' => [
				'req' => [
					'nAdr' => self::ADDRESS,
					'param' => [
						'type' => 0,
						'data' => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
					],
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->setAccessPassword(self::ADDRESS, '000102030405060708090A0B0C0D0E0F', DataFormat::HEX);
		});
	}

	/**
	 * Tests the function to set an access password in HEX format (short length)
	 */
	public function testSetAccessPasswordHexShort(): void {
		$request = [
			'mType' => 'iqrfEmbedOs_SetSecurity',
			'data' => [
				'req' => [
					'nAdr' => self::ADDRESS,
					'param' => [
						'type' => 0,
						'data' => [1, 2, 3, 4, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
					],
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->setAccessPassword(self::ADDRESS, '01020304', DataFormat::HEX);
		});
	}

	/**
	 * Tests the function to set an access password in ASCII format (short length)
	 */
	public function testSetAccessPasswordAsciiShort(): void {
		$request = [
			'mType' => 'iqrfEmbedOs_SetSecurity',
			'data' => [
				'req' => [
					'nAdr' => self::ADDRESS,
					'param' => [
						'type' => 0,
						'data' => [49, 50, 51, 52, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
					],
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->setAccessPassword(self::ADDRESS, '1234', DataFormat::ASCII);
		});
	}


	/**
	 * Tests the function to set an user key in ASCII format (max length)
	 */
	public function testSetUserKeyAsciiMax(): void {
		$request = [
			'mType' => 'iqrfEmbedOs_SetSecurity',
			'data' => [
				'req' => [
					'nAdr' => self::ADDRESS,
					'param' => [
						'type' => 1,
						'data' => [48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 65, 66, 67, 68, 69, 70],
					],
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->setUserKey(self::ADDRESS, '0123456789ABCDEF', DataFormat::ASCII);
		});
	}

}

$test = new SecurityManagerTest();
$test->run();
