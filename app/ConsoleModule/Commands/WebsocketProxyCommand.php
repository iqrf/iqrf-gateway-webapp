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
use Ratchet\App;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
		// Create Ratchet app
		$app = new App(
			httpHost: $config->host,
			port: $config->port,
			address: $config->address,
		);
		// Register connection handler
		$app->route(
			path: '',
			controller: new ProxyHandler(
				upstreamUrl: $config->upstream,
				upstreamToken: $config->token,
				authenticator: $this->authenticator,
				loggerManager: $this->loggerManager,
			),
			allowedOrigins: ['*'],
		);
		// Run server
		$app->run();
		return 0;
	}

}
