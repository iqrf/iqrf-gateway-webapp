<?php

/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
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

namespace App\ConsoleModule\Commands;

use App\ApiModule\Version0\Models\BearerAuthenticator;
use App\Models\WebSocket\ProxyConfigManager;
use App\Models\WebSocket\ProxyHandler;
use Contributte\Monolog\LoggerManager;
use Ratchet\Http\HttpServer;
use Ratchet\Http\OriginCheck;
use Ratchet\Http\Router;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\Socket\SocketServer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

#[AsCommand(name: 'websocket:proxy:run', description: 'Starts websocket proxy')]
class WebsocketProxyCommand extends Command {

	/**
	 * Constructor
	 * @param ProxyConfigManager $configManager Config manager
	 * @param LoggerManager $loggerManager Logger manager
	 * @param BearerAuthenticator $authenticator Authenticator
	 */
	public function __construct(
		private readonly ProxyConfigManager $configManager,
		private readonly LoggerManager $loggerManager,
		private readonly BearerAuthenticator $authenticator,
	) {
		parent::__construct();
	}

	/**
	 * Runs the WebSocket server
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$style = new SymfonyStyle($input, $output);
		// get config
		$config = $this->configManager->readConfig();
		// check token
		if ($config->token === '') {
			$style->error('API token in proxy configuration is empty or missing.');
			return 1;
		}
		$style->info('Starting server at ' . $config->host . ':' . strval($config->port));
		// Setup routes
		$routes = new RouteCollection();
		// Register proxy route
		$this->registerRoute(
			routes: $routes,
			path: '',
			handler: new ProxyHandler(
				upstreamUrl: $config->upstream,
				upstreamToken: $config->token,
				authenticator: $this->authenticator,
				loggerManager: $this->loggerManager,
			),
			httpHost: $config->host,
		);
		// Setup server
		$loop = Loop::get();
		$socket = new SocketServer(
			uri: $config->address . ':' . $config->port,
			context: [],
			loop: $loop,
		);
		$server = new IoServer(
			app: new HttpServer(
				new Router(
					new UrlMatcher(
						routes: $routes,
						context: new RequestContext(),
					),
				),
			),
			socket: $socket,
			loop: $loop,
		);
		$server->run();
		return 0;
	}

	/**
	 * Register route with a handler
	 * @param RouteCollection $routes Route collection
	 * @param string $path Route path
	 * @param object $handler Controller / route handler
	 * @param string $httpHost HTTP host
	 * @param array<string> $allowedOrigins Allowed origins
	 */
	private function registerRoute(
		RouteCollection $routes,
		string $path,
		object $handler,
		string $httpHost,
		array $allowedOrigins = ['*'],
	): void {
		$ws = new WsServer($handler);
		if ($allowedOrigins !== [] && $allowedOrigins[0] !== '*') {
			$ws = new OriginCheck($ws, $allowedOrigins);
		}
		$routes->add(
			name: $path,
			route: new Route(
				path: $path,
				defaults: ['_controller' => $ws],
				requirements: ['Origin' => $httpHost],
				options: [],
				host: $httpHost,
				schemes: [],
				methods: ['GET'],
			),
		);
	}

}
