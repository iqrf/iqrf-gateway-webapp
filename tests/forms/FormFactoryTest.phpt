<?php

/**
 * TEST: App\Model\ConfigManager
 * @phpVersion >= 5.6
 * @testCase
 */
use App\Forms\FormFactory;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

class FormFactoryTest extends TestCase {

	private $container;

	function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * @test
	 * Test function to create a form
	 */
	public function testCreate() {
		$formFactory = new FormFactory();
		$form = new Form();
		Assert::equal($form, $formFactory->create());
	}

}

$test = new FormFactoryTest($container);
$test->run();
