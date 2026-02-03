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

namespace Tests\Toolkit;

use App\ApiModule\Version0\Models\BearerAuthenticator;
use App\Models\Database\Entities\User;
use App\Models\WebSocket\ProxyHandler;
use Contributte\Monolog\LoggerManager;
use InvalidArgumentException;
use Mockery;
use Psr\Log\LoggerInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

require __DIR__ . '/../bootstrap.php';

$url = $argv[1] ?? 'ws://localhost:1338';
$port = $argv[2] ?? 9595;

$logger = Mockery::mock(LoggerInterface::class);
$logger->shouldIgnoreMissing();

$loggerManager = Mockery::mock(LoggerManager::class);
$loggerManager->shouldReceive('get')
	->withArgs(['proxy'])
	->andReturn($logger);

$authenticator = Mockery::mock(BearerAuthenticator::class);
$authenticator->shouldReceive('authenticateUser')
	->withArgs(['invalidFormatToken'])
	->andThrow(InvalidArgumentException::class);
$authenticator->shouldReceive('authenticateUser')
	->withArgs(['invalidToken'])
	->andReturn(null);
$authenticator->shouldReceive('authenticateUser')
	->withArgs(['validToken'])
	->andReturn(User::class);

$server = IoServer::factory(
	new HttpServer(
		new WsServer(
			new ProxyHandler(
				upstreamUrl: $url,
				upstreamToken: '',
				authenticator: $authenticator,
				loggerManager: $loggerManager,
			),
		),
	),
	$port,
);

$server->run();
