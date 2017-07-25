<?php

/**
 * TEST: App\RouterFactory
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\Router;

use App\RouterFactory;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

class RouterFactoryTest extends TestCase {

	private $container;

	function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * @test
	 * Test function to create a router
	 */
	public function testCreateRouter() {
		/** @var RouteList $routeList */
		$routeList = RouterFactory::createRouter();
		Assert::type(RouteList::class, $routeList);
		Assert::same('', $routeList->getModule());
		Assert::same(['[<lang [a-z]{2}>/]<presenter>/<action>[/<id>]'], array_map(function (Route $route) {
				return $route->getMask();
			}, (array) $routeList->getIterator()));
	}

}

$test = new RouterFactoryTest($container);
$test->run();
