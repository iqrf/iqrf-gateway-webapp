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

namespace App\ConfigModule\Utils;

use Nette\Utils\Strings;

/**
 * Conf file parsing utility
 */
class ConfParser {

	/**
	 * Converts conf file configuration from string to array
	 * @param string $content Conf file content
	 * @return array<string, string|array<string, string>>|null Configuration array
	 */
	public static function toArray(string $content): ?array {
		$config = Strings::replace($content, [
			'/^#/' => ';',
			'/\\n#/' => PHP_EOL . ';',
			'/\(/' => '"("',
			'/\)/' => '")"',
		]);
		$config = parse_ini_string($config, true, INI_SCANNER_TYPED);
		return $config === false ? null : $config;
	}

	/**
	 * Converts conf file configuration from array to string
	 * @param array<string, array<string, mixed>> $config Configuration array
	 * @return string|null Configuration string
	 */
	public static function toConf(array $config): ?string {
		$output = [];
		foreach ($config as $key => $array) {
			$output[] = '[' . $key . ']';
			foreach ($array as $arrKey => $arrVal) {
				if ($arrVal === null || $arrVal === '') {
					$output[] = '#' . $arrKey . '=';
				} else {
					$output[] = $arrKey . '=' . strval($arrVal);
				}
			}
			$output[] = PHP_EOL;
		}
		if ($output === []) {
			return null;
		}
		return implode(PHP_EOL, $output);
	}

}
