<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
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

namespace App\Router;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class RouterFactory {

	use Nette\StaticClass;

	/**
	 * Create router
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter() {
		$router = new RouteList();
		$config = new RouteList('Config');
		$config[] = new Route('[<lang [a-z]{2}>/]config/<presenter>/<action>[/<id>]', 'Homepage:default');
		$router[] = $config;
		$iqrfApp = new RouteList('IqrfApp');
		$iqrfApp[] = new Route('[<lang [a-z]{2}>/]iqrfapp/<presenter>/<action>', 'Homepage:default');
		$router[] = $iqrfApp;
		$service = new RouteList('Service');
		$service[] = new Route('[<lang [a-z]{2}>/]service/<presenter>/<action>', 'Control:default');
		$router[] = $service;
		$router[] = new Route('[<lang [a-z]{2}>/]<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}

}
