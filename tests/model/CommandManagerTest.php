<?php

/**
 * TEST: App\Model\CommandManager
 * @covers App\Model\CommandManager
 * @phpVersion >= 5.6
 * @testCase
 */
declare(strict_types=1);

namespace Test\Model;

use App\Model\CommandManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

class CommandManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var CommandManager Command manager
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
		$this->manager = new CommandManager(false);
	}

	/**
	 * @test
	 * Test function to execute a shell command
	 */
	public function testSend() {
		Assert::same('OK', $this->manager->send('echo "OK"'));
	}

	/**
	 * @test
	 * Test function to check the existence of a command
	 */
	public function testCommandExist() {
		Assert::true($this->manager->commandExist('echo'));
		Assert::false($this->manager->commandExist('sndikasdhisdbajdbas'));
	}

}

$test = new CommandManagerTest($container);
$test->run();
