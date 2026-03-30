<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

use App\Kernel;
use Contributte\Tester\Environment;

require __DIR__ . '/../vendor/autoload.php';

$configurator = Kernel::boot();
$configurator->setDebugMode(false);
$configurator->setTempDirectory(__DIR__ . '/tmp');

Environment::setupTester();
Environment::setupTimezone('Etc/GMT-2');
Environment::setupFolders(__DIR__);
if (basename(__DIR__) === 'tests') {
	if (!defined('TESTER_DIR')) {
		define('TESTER_DIR', realpath(__DIR__));
	}
	if (!defined('TMP_DIR')) {
		define('TMP_DIR', TESTER_DIR . '/tmp');
	}
	@mkdir(TMP_DIR);
	$dirs = [
		'certificates/',
		'configuration/',
		'configuration/scheduler/',
		'controller/',
		'maintenance/',
		'translator/',
		'zip/',
	];
	foreach ($dirs as $dir) {
		@mkdir(TMP_DIR . '/' . $dir);
	}
}

return $configurator->createContainer();
