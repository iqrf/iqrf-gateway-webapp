<?php

/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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
use Rector\CodeQualityStrict\Rector\If_\MoveOutMethodCallInsideIfConditionRector;
use Rector\CodeQualityStrict\Rector\Variable\MoveVariableDeclarationNearReferenceRector;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
	$parameters = $containerConfigurator->parameters();

	$parameters->set(Option::SKIP, [
		CallableThisArrayToAnonymousFunctionRector::class,
		MoveVariableDeclarationNearReferenceRector::class,
		MoveOutMethodCallInsideIfConditionRector::class,
	]);

	$parameters->set(Option::PATHS, [
		__DIR__ . '/app',
		__DIR__ . '/bin',
		__DIR__ . '/tests',
		]);

	$parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_73);

	$parameters->set(Option::SETS, [
		SetList::CODE_QUALITY,
		SetList::CODE_QUALITY_STRICT,
		SetList::DOCTRINE_CODE_QUALITY,
		SetList::DOCTRINE_DBAL_211,
		SetList::NETTE_30,
		SetList::NETTE_31,
		SetList::NETTE_UTILS_CODE_QUALITY,
		SetList::PHP_73,
		SetList::PHP_74,
		SetList::SYMFONY_52,
		SetList::TYPE_DECLARATION,
	]);
};
