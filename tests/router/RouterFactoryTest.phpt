<?php

/**
 * TEST: App\RouterFactory
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\Router;

use App\Router\RouterFactory;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

class RouterFactoryTest extends TestCase {

	private $container;

	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * @test
	 * Test function to create a router
	 */
	public function testCreateRouter() {
		/** @var RouteList $routeList */
		$routeList = RouterFactory::createRouter();
		$expected = [
			['Config:' => '[<lang [a-z]{2}>/]config/<presenter>/<action>[/<id>]'],
			['IqrfApp:' => '[<lang [a-z]{2}>/]iqrfapp/<presenter>/<action>'],
			['Service:' => '[<lang [a-z]{2}>/]service/<presenter>/<action>'],
			'[<lang [a-z]{2}>/]<presenter>/<action>[/<id>]',
		];
		Assert::type(RouteList::class, $routeList);
		Assert::same('', $routeList->getModule());
		Assert::same($expected, array_map(function ($type) {
					if ($type instanceof Route) {
						return $type->getMask();
					} elseif ($type instanceof RouteList) {
						return [$type->getModule() => array_map(function ($route) {
										return $route->getMask();
									}, (array) $type->getIterator())[0]];
					}
				}, (array) $routeList->getIterator()));
	}

}

$test = new RouterFactoryTest($container);
$test->run();
