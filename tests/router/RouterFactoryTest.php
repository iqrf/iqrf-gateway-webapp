<?php

/**
 * TEST: App\Router\RouterFactory
 * @covers App\Router\RouterFactory
 * @phpVersion >= 5.6
 * @testCase
 */
declare(strict_types=1);

namespace Test\Router;

use App\Router\RouterFactory;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

class RouterFactoryTest extends TestCase {

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
	 * @test
	 * Test function to create a router
	 */
	public function testCreateRouter() {
		/** @var RouteList $routeList */
		$routeList = RouterFactory::createRouter();
		$expected = [
			['Cloud:' => '[<lang [a-z]{2}>/]cloud/<presenter>/<action>[/<id>]'],
			['Config:' => '[<lang [a-z]{2}>/]config/<presenter>/<action>[/<id>]'],
			['Gateway:' => '[<lang [a-z]{2}>/]gateway/<presenter>/<action>'],
			['IqrfApp:' => '[<lang [a-z]{2}>/]iqrfnet/<presenter>/<action>'],
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
