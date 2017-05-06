<?php

/**
 * TEST: App\Model\CommandManager
 * @phpVersion >= 5.6
 * @testCase
 */
use App\Model\CommandManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

class CommandManagerTest extends TestCase {

	private $container;

	function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * @test
	 * Test function to execute a shell command
	 */
	public function testSend() {
		$manager = new CommandManager(false);
		Assert::same('OK', $manager->send('echo "OK"'));
	}

}

$test = new CommandManagerTest($container);
$test->run();
