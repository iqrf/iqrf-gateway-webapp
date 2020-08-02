<?php

/**
 * TEST: App\IqrfNetModule\Entities\StandardBinaryOutput
 * @covers App\IqrfNetModule\Entities\StandardBinaryOutput
 * @phpVersion >= 7.2
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
final class StandardBinaryOutputTest extends TestCase {

	/**
	 * IQRF Standard binary output index
	 */
	private const INDEX = 0;

	/**
	 * IQRF Standard binary output's state
	 */
	private const STATE = true;

	/**
	 * @var StandardBinaryOutput IQRF Standard binary output entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->entity = new StandardBinaryOutput(self::INDEX, self::STATE);
	}

	/**
	 * Tests the function to get binary output's state
	 */
	public function testGetState(): void {
		Assert::same(self::STATE, $this->entity->getState());
	}

	/**
	 * Tests the function to set binary outputs's state
	 */
	public function testSetState(): void {
		$newState = false;
		$this->entity->setState($newState);
		Assert::same($newState, $this->entity->getState());
	}

	/**
	 * Tests the function to convert the entity to an array
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'index' => self::INDEX,
			'state' => self::STATE,
		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

}

$test = new StandardBinaryOutputTest();
$test->run();
