<?php

/**
 * TEST: App\IqrfNetModule\Entities\StandardBinaryOutput
 * @covers App\IqrfNetModule\Entities\StandardBinaryOutput
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Entities;

use App\IqrfNetModule\Entities\StandardBinaryOutput;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for IQRF Standard binary output entity
 */
class StandardBinaryOutputTest extends TestCase {

	/**
	 * @var int IQRF Standard binary output index
	 */
	private $index = 0;

	/**
	 * @var StandardBinaryOutput IQRF Standard binary output entity
	 */
	private $entity;

	/**
	 * @var bool IQRF Standard binary output's state
	 */
	private $state = true;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->entity = new StandardBinaryOutput($this->index, $this->state);
	}

	/**
	 * Tests the function to get binary output's state
	 */
	public function testGetState(): void {
		Assert::same($this->state, $this->entity->getState());
	}

	/**
	 * Tests the function to set binary outputs's state
	 */
	public function testSetState(): void {
		$this->entity->setState(!$this->state);
		Assert::same(!$this->state, $this->entity->getState());
	}

	/**
	 * Tests the function to convert the entity to an array
	 */
	public function testToArray(): void {
		$expected = [
			'index' => $this->index,
			'state' => $this->state,
		];
		Assert::same($expected, $this->entity->toArray());
	}

}

$test = new StandardBinaryOutputTest();
$test->run();
