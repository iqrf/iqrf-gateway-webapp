<?php

/**
 * TEST: App\IqrfAppModule\Model\MessageIdManager
 * @covers App\IqrfAppModule\Model\MessageIdManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\IqrfAppModule\Model;

use App\IqrfAppModule\Model\MessageIdManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for message ID generator
 */
class MessageIdManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Test function to generate message ID
	 */
	public function testGenerate() {
		$expected = strval((new \DateTime())->getTimestamp());
		$manager = new MessageIdManager();
		Assert::same($expected, $manager->generate());
	}

}


$test = new MessageIdManagerTest($container);
$test->run();
