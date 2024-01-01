<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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
use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Transform\Rector\Attribute\AttributeKeyToClassConstFetchRector;

return static function (RectorConfig $rectorConfig): void {
	$rectorConfig->paths([
		__DIR__ . '/app',
		__DIR__ . '/bin',
		__DIR__ . '/tests',
	]);

	$rectorConfig->phpVersion(PhpVersion::PHP_81);

	// register a single rule
	$rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

	// define sets of rules
	$rectorConfig->sets([
		DoctrineSetList::DOCTRINE_CODE_QUALITY,
		DoctrineSetList::DOCTRINE_COMMON_20,
		DoctrineSetList::DOCTRINE_DBAL_30,
		DoctrineSetList::DOCTRINE_ORM_214,
		SetList::CODE_QUALITY,
		SetList::CODING_STYLE,
		SetList::DEAD_CODE,
		SetList::PHP_81,
		SetList::TYPE_DECLARATION,
		SetList::INSTANCEOF,
		SymfonySetList::SYMFONY_63,
		SymfonySetList::SYMFONY_CODE_QUALITY,
	]);

	$rectorConfig->skip([
		AttributeKeyToClassConstFetchRector::class,
		CallableThisArrayToAnonymousFunctionRector::class,
		CatchExceptionNameMatchingTypeRector::class,
		FlipTypeControlToUseExclusiveTypeRector::class,
		NewlineAfterStatementRector::class,
		NewlineBeforeNewAssignSetRector::class,
		PostIncDecToPreIncDecRector::class,
		SplitDoubleAssignRector::class,
		SymplifyQuoteEscapeRector::class,
		__DIR__ . '/tests/tmp',
	]);

};
