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

use Apitte\Core\Application\IApplication as ApiApplication;
use App\Kernel;
use Nette\Application\Application as UiApplication;

require_once __DIR__ . '/../vendor/autoload.php';

$isApi = substr($_SERVER['REQUEST_URI'], 0, 4) === '/api';

// Creates DI container
$container = Kernel::boot()->createContainer();
// Gets application from DI container
if ($isApi) {
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: Accept, Content-Type, Authorization');
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT');
	$application = $container->getByType(ApiApplication::class);
} else {
	$application = $container->getByType(UiApplication::class);
}
// Runs application
$application->run();
