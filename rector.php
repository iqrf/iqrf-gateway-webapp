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

use Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector;
use Rector\SOLID\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\SOLID\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
	$parameters = $containerConfigurator->parameters();

	$parameters->set('exclude_rectors', [
		CallableThisArrayToAnonymousFunctionRector::class,
		FinalizeClassesWithoutChildrenRector::class,
		UseInterfaceOverImplementationInConstructorRector::class,
	]);

	$parameters->set('paths', [
		__DIR__ . '/app',
		__DIR__ . '/bin',
		__DIR__ . '/tests',
		]);

	$parameters->set('php_version_features', '7.2');

	$parameters->set('sets', [
		'code-quality',
		'doctrine-code-quality',
		'doctrine-dbal210',
		'nette30',
		'nette-utils-code-quality',
		'php72',
		'php73',
		'php74',
		'symfony50',
		'solid',
	]);
};
