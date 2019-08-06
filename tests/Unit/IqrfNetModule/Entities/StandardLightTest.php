<?php

/**
 * TEST: App\IqrfNetModule\Entities\StandardLight
 * @covers App\IqrfNetModule\Entities\StandardLight
 * @phpVersion >= 7.1
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
class StandardLightTest extends TestCase {

	/**
	 * @var int IQRF Standard light index
	 */
	private $index = 0;

	/**
	 * @var StandardLight IQRF Standard light entity
	 */
	private $entity;

	/**
	 * @var int IQRF Standard light power
	 */
	private $power = 50;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->entity = new StandardLight($this->index, $this->power);
	}

	/**
	 * Tests the function to get light's power
	 */
	public function testGetPower(): void {
		Assert::same($this->power, $this->entity->getPower());
	}

	/**
	 * Tests the function to set light's power
	 */
	public function testSetPower(): void {
		$this->entity->setPower($this->power / 2);
		Assert::same($this->power / 2, $this->entity->getPower());
	}

	/**
	 * Tests the function to convert the entity to an array
	 */
	public function testToArray(): void {
		$expected = [
			'index' => $this->index,
			'power' => $this->power,
		];
		Assert::same($expected, $this->entity->toArray());
	}

}

$test = new StandardLightTest();
$test->run();
