<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();
@mkdir(__DIR__ . '/configuration-test/');
date_default_timezone_set('Europe/Prague');

$configurator = new Nette\Configurator;
$configurator->setDebugMode(false);
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()->addDirectory(__DIR__ . '/../app')->register();
$configurator->addConfig(__DIR__ . '/../app/config/config.neon');

$container = $configurator->createContainer();
return $container;
