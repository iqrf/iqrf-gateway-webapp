<?php

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();
Tester\Helpers::purge(__DIR__ . '/configuration-test/');
date_default_timezone_set('Europe/Prague');

$configurator = new Nette\Configurator;
$configurator->setDebugMode(false);
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()->addDirectory(__DIR__ . '/../app')->register();
$configurator->addConfig(__DIR__ . '/../app/config/config.neon');
return $configurator->createContainer();
