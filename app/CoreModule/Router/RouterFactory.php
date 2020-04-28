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
use Nette\Application\Routers\RouteList;
use Nette\Routing\Router;

/**
 * Router factory
 */
final class RouterFactory {

	use Nette\StaticClass;

	/**
	 * Create router
	 * @return Router Router interface
	 */
	public static function createRouter(): Router {
		$router = new RouteList();
		$cloud = $router->withModule('Cloud');
		$cloud->addRoute('[<lang [a-z]{2}>/]cloud/<presenter>/<action>[/<id>]', 'Homepage:default');
		$config = $router->withModule('Config');
		$config->addRoute('[<lang [a-z]{2}>/]config/scheduler/add/<type>', 'Scheduler:add');
		$config->addRoute('[<lang [a-z]{2}>/]config/<presenter>/<action>[/<id>]', 'Homepage:default');
		$install = $router->withModule('Install');
		$install->addRoute('[<lang [a-z]{2}>/]install/<presenter>/<action>', 'Homepage:default');
		$iqrfNet = $router->withModule('IqrfNet');
		$iqrfNet->addRoute('[<lang [a-z]{2}>/]iqrfnet/enumeration/<address>', 'Enumeration:default');
		$iqrfNet->addRoute('[<lang [a-z]{2}>/]iqrfnet/tr-config/<address>', 'TrConfig:default');
		$iqrfNet->addRoute('[<lang [a-z]{2}>/]iqrfnet/<presenter>/<action>', 'Homepage:default');
		$network = $router->withModule('Network');
		$network->addRoute('[<lang [a-z]{2}>/]network/<presenter>/<action>[/<uuid>]', 'Homepage:default');
		$service = $router->withModule('Service');
		$service->addRoute('[<lang [a-z]{2}>/]gateway/ssh', [
			'presenter' => 'Control',
			'action' => 'default',
			'name' => 'ssh',
		], $router::ONE_WAY);
		$service->addRoute('[<lang [a-z]{2}>/]gateway/unattended-upgrades', [
			'presenter' => 'Control',
			'action' => 'default',
			'name' => 'unattended-upgrades',
		], $router::ONE_WAY);
		$service->addRoute('[<lang [a-z]{2}>/]service', [
			'presenter' => 'Control',
			'action' => 'default',
			'name' => 'iqrf-gateway-daemon',
		], $router::ONE_WAY);
		$service->addRoute('[<lang [a-z]{2}>/]service/<name>', 'Control:default');
		$gateway = $router->withModule('Gateway');
		$gateway->addRoute('[<lang [a-z]{2}>/]gateway/<presenter>/<action>', 'Homepage:default');
		$core = $router->withModule('Core');
		$core->addRoute('[<lang [a-z]{2}>/]<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}

}
