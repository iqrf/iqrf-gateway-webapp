<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types = 1);

namespace App\CoreModule\Router;

use Nette;
use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

/**
 * Router factory
 */
final class RouterFactory {

	use Nette\StaticClass;

	/**
	 * Create router
	 * @return IRouter Router interface
	 */
	public static function createRouter(): IRouter {
		$router = new RouteList();
		$cloud = new RouteList('Cloud');
		$cloud[] = new Route('[<lang [a-z]{2}>/]cloud/<presenter>/<action>[/<id>]', 'Homepage:default');
		$router[] = $cloud;
		$config = new RouteList('Config');
		$config[] = new Route('[<lang [a-z]{2}>/]config/scheduler/add/<type>', 'Scheduler:add');
		$config[] = new Route('[<lang [a-z]{2}>/]config/<presenter>/<action>[/<id>]', 'Homepage:default');
		$router[] = $config;
		$gateway = new RouteList('Gateway');
		$gateway[] = new Route('[<lang [a-z]{2}>/]gateway/<presenter>/<action>', 'Homepage:default');
		$router[] = $gateway;
		$install = new RouteList('Install');
		$install[] = new Route('[<lang [a-z]{2}>/]install/<presenter>/<action>', 'Homepage:default');
		$router[] = $install;
		$iqrfNet = new RouteList('IqrfNet');
		$iqrfNet[] = new Route('[<lang [a-z]{2}>/]iqrfnet/dpa-config/<address>', 'DpaConfig:default');
		$iqrfNet[] = new Route('[<lang [a-z]{2}>/]iqrfnet/enumeration/<address>', 'Enumeration:default');
		$iqrfNet[] = new Route('[<lang [a-z]{2}>/]iqrfnet/os-config/<address>', 'OsConfig:default');
		$iqrfNet[] = new Route('[<lang [a-z]{2}>/]iqrfnet/tr-security/<address>', 'TrSecurity:default');
		$iqrfNet[] = new Route('[<lang [a-z]{2}>/]iqrfnet/<presenter>/<action>', 'Homepage:default');
		$router[] = $iqrfNet;
		$service = new RouteList('Service');
		$service[] = new Route('[<lang [a-z]{2}>/]service/<presenter>/<action>', 'Control:default');
		$router[] = $service;
		$core = new RouteList('Core');
		$core[] = new Route('[<lang [a-z]{2}>/]<presenter>/<action>[/<id>]', 'Homepage:default');
		$router[] = $core;
		return $router;
	}

}
