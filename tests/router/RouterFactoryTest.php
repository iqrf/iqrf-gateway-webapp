<?php

/**
 * TEST: App\Router\RouterFactory
 * @covers App\Router\RouterFactory
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\Router;

use App\Router\RouterFactory;
use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * Tests for router factory
 */
class RouterFactoryTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var array Routes
	 */
	private $expected = [
		['Cloud:' => [
				'[<lang [a-z]{2}>/]cloud/<presenter>/<action>[/<id>]',
			],],
		['Config:' => [
				'[<lang [a-z]{2}>/]config/scheduler/add/<type>',
				'[<lang [a-z]{2}>/]config/<presenter>/<action>[/<id>]',
			],],
		['Gateway:' => [
				'[<lang [a-z]{2}>/]gateway/<presenter>/<action>',
			],],
		['IqrfApp:' => [
				'[<lang [a-z]{2}>/]iqrfnet/<presenter>/<action>',
			],],
		['Service:' => [
				'[<lang [a-z]{2}>/]service/<presenter>/<action>',
			],],
		['Core:' =>
			['[<lang [a-z]{2}>/]<presenter>/<action>[/<id>]']
		],
	];

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Test function to create a router
	 */
	public function testCreateRouter() {
		/** @var RouteList $routeList */
		$routeList = RouterFactory::createRouter();
		Assert::type(RouteList::class, $routeList);
		Assert::same('', $routeList->getModule());
		Assert::same($this->expected, array_map(function (IRouter $type) {
					if ($type instanceof Route) {
						return $type->getMask();
					} elseif ($type instanceof RouteList) {
						$routeMask = array_map(function (Route $route) {
							return $route->getMask();
						}, (array) $type->getIterator());
						return [$type->getModule() => $routeMask];
					}
				}, (array) $routeList->getIterator()));
	}

}

$test = new RouterFactoryTest($container);
$test->run();
