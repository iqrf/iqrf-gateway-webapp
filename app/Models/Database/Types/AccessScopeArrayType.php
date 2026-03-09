<?php

/**
 * Copyright 2023-2026 IQRF Tech s.r.o.
 * Copyright 2023-2026 MICRORISC s.r.o.
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

namespace App\Models\Database\Types;

use App\Enums\AccessScope;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

/**
 * Acess scope array type
 */
class AccessScopeArrayType extends Type {

	/**
	 * Type name
	 */
	public const ACCESS_SCOPE_ARRAY = 'access_scope_array';

	/**
	 * {@inheritDoc}
	 */
	// phpcs:ignore Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps
	public function getSQLDeclaration(array $column, AbstractPlatform $platform): string {
		return $platform->getJsonTypeDeclarationSQL($column);
	}

	/**
	 * Converts a value from its PHP representation to its database representation
	 * of this type.
	 *
	 * @param mixed $value The value to convert.
	 * @param AbstractPlatform $platform The currently used database platform.
	 * @return string The database representation of the value.
	 * @throws ConversionException Thrown if conversion fails
	 */
	public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string {
		if ($value === null) {
			return null;
		}

		if (!is_array($value)) {
			throw new ConversionException('Value should be an array of strings.');
		}

		$converted = array_map(
			static fn (AccessScope $member): string => $member->value,
			$value,
		);

		return json_encode($converted);
	}

	/**
	 * Converts a value from its database representation to its PHP representation
	 * of this type.
	 *
	 * @param mixed $value The value to convert.
	 * @param AbstractPlatform $platform The currently used database platform.
	 * @return array<AccessScope> The PHP representation of the value.
	 * @throws ConversionException Thrown if conversion fails
	 */
	// phpcs:ignore Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps
	public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?array {
		if ($value === null || $value === '') {
			return null;
		}

		if (!is_string($value)) {
			throw new ConversionException('Value must be a JSON string.');
		}

		$data = json_decode($value, true);
		if (!is_array($data)) {
			throw new ConversionException('Decoded JSON string should be an array of strings.');
		}

		return array_map(
			function (string $val): AccessScope {
				$converted = AccessScope::tryFrom($val);
				if ($converted === null) {
					throw new ConversionException('String value must be a member of AccessScope enum.');
				}
				return $converted;
			},
			$data,
		);
	}

	/**
	 * Returns type name
	 * @return string Type name
	 */
	public function getName(): string {
		return self::ACCESS_SCOPE_ARRAY;
	}

}
