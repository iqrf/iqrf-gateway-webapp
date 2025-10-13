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

use Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector;
use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Symfony\Symfony73\Rector\Class_\InvokableCommandInputAttributeRector;

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/app',
		__DIR__ . '/bin',
		__DIR__ . '/tests',
	])
	->withSkip([
		CatchExceptionNameMatchingTypeRector::class,
		FirstClassCallableRector::class,
		InvokableCommandInputAttributeRector::class,
		NewlineAfterStatementRector::class,
		NewlineBeforeNewAssignSetRector::class,
		NullToStrictStringFuncCallArgRector::class,
		SplitDoubleAssignRector::class,
		SymplifyQuoteEscapeRector::class,
		__DIR__ . '/tests/tmp',
	])
	->withPHPStanConfigs([
		__DIR__ . '/phpstan.neon',
	])
	->withPhpVersion(80200)
	->withPhpSets(php82: true)
	->withPreparedSets(
		deadCode: true,
		codeQuality: true,
		codingStyle: true,
		typeDeclarations: true,
		instanceOf: true,
	)
	->withAttributesSets(all: true)
	->withComposerBased(
		doctrine: true,
		symfony: true,
		netteUtils: true,
	)
	->withIndent("\t");
