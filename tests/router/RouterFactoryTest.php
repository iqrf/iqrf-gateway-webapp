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
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

/**
 * Tests for router factory
 */
class RouterFactoryTest extends TestCase {

	/**
	 * @var array[string[]] Routes
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
		['Install:' => [
			'[<lang [a-z]{2}>/]install/<presenter>/<action>',
		],],
		['IqrfNet:' => [
			'[<lang [a-z]{2}>/]iqrfnet/enumeration/<address>',
			'[<lang [a-z]{2}>/]iqrfnet/<presenter>/<action>',
		],],
		['Service:' => [
			'[<lang [a-z]{2}>/]service/<presenter>/<action>',
		],],
		['Core:' =>
			['[<lang [a-z]{2}>/]<presenter>/<action>[/<id>]'],
		],
	];

	/**
	 * Test function to create a router
	 */
	public function testCreateRouter(): void {
		/** @var RouteList $routeList */
		$routeList = RouterFactory::createRouter();
		Assert::type(RouteList::class, $routeList);
		Assert::same('', $routeList->getModule());
		Assert::same($this->expected, array_map(function (IRouter $type) {
			if ($type instanceof Route) {
				return $type->getMask();
			} else {
				if ($type instanceof RouteList) {
					$routeMask = array_map(function (Route $route) {
						return $route->getMask();
					}, (array) $type->getIterator());
					return [$type->getModule() => $routeMask];
				}
			}
			return;
		}, (array) $routeList->getIterator()));
	}

}

$test = new RouterFactoryTest();
$test->run();
