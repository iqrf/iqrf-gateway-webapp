<?php

declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();
if (basename(__DIR__) === 'tests') {
	$tempDir = __DIR__ . '/temp/';
	@mkdir($tempDir);
	@mkdir($tempDir . 'certificates/');
	@mkdir($tempDir . 'configuration/');
	@mkdir($tempDir . 'configuration/scheduler/');
	@mkdir($tempDir . 'zip/');
}
date_default_timezone_set('Europe/Prague');

$configurator = new Nette\Configurator;
$configurator->setDebugMode(false);
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()->addDirectory(__DIR__ . '/../app')->register();
$configurator->addConfig(__DIR__ . '/../app/config/config.neon');

$container = $configurator->createContainer();
return $container;
