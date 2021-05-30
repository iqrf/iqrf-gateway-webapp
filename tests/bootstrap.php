<?php

declare(strict_types = 1);

use App\Kernel;

require __DIR__ . '/../vendor/autoload.php';

$configurator = Kernel::boot();
$configurator->setDebugMode(false);
$configurator->setTempDirectory(__DIR__ . '/../temp/tests');

Tester\Environment::setup();
if (basename(__DIR__) === 'tests') {
	$tempDir = __DIR__ . '/temp/';
	@mkdir($tempDir);
	@mkdir($tempDir . 'certificates/');
	@mkdir($tempDir . 'configuration/');
	@mkdir($tempDir . 'configuration/scheduler/');
	@mkdir($tempDir . 'controller/');
	@mkdir($tempDir . 'maintenance/');
	@mkdir($tempDir . 'translator/');
	@mkdir($tempDir . 'zip/');
}
date_default_timezone_set('Etc/GMT-2');

return $configurator->createContainer();
