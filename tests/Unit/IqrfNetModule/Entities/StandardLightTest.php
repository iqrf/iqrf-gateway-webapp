<?php

/**
 * TEST: App\IqrfNetModule\Entities\StandardLight
 * @covers App\IqrfNetModule\Entities\StandardLight
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Entities;

use App\IqrfNetModule\Entities\StandardLight;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for IQRF Standard light entity
 */
final class StandardLightTest extends TestCase {

	/**
	 * IQRF Standard light index
	 */
	private const INDEX = 0;

	/**
	 * IQRF Standard light power
	 */
	private const POWER = 50;

	/**
	 * @var StandardLight IQRF Standard light entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->entity = new StandardLight(self::INDEX, self::POWER);
	}

	/**
	 * Tests the function to get light's power
	 */
	public function testGetPower(): void {
		Assert::same(self::POWER, $this->entity->getPower());
	}

	/**
	 * Tests the function to set light's power
	 */
	public function testSetPower(): void {
		$this->entity->setPower(self::POWER / 2);
		Assert::same(self::POWER / 2, $this->entity->getPower());
	}

	/**
	 * Tests the function to convert the entity to an array
	 */
	public function testToArray(): void {
		$expected = [
			'index' => self::INDEX,
			'power' => self::POWER,
		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

}

$test = new StandardLightTest();
$test->run();
