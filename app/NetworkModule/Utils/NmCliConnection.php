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

namespace App\NetworkModule\Utils;

use BackedEnum;
use Nette\Utils\Strings;
use function explode;
use function sprintf;

/**
 * NetworkManager CLI connection utils
 */
class NmCliConnection {

	/**
	 * Decodes nmcli connection configuration
	 * @param string $data nmcli connection configuration
	 * @return array<string, array<string|array<string>>> Connection configuration
	 */
	public static function decode(string $data): array {
		$config = [];
		if ($data === '') {
			return $config;
		}
		$array = explode(PHP_EOL, Strings::trim($data));
		foreach ($array as $row) {
			$temp = explode(':', $row, 2);
			if ($temp[0] === '') {
				continue;
			}
			[$section, $key] = explode('.', $temp[0], 2);
			if (str_contains($section, '[')) {
				[$section, $sectionID] = Strings::split($section, '#\[(\d+)\]#');
				$config[$section] ??= [];
				$output = &$config[$section][((int) $sectionID) - 1];
			} else {
				$output = &$config[$section];
			}
			if (str_contains($key, '[')) {
				[$key, $keyId] = Strings::split($key, '#\[(\d+)\]#');
				$output[$key][((int) $keyId) - 1] = $temp[1];
			} else {
				$output[$key] = $temp[1];
			}
		}
		return $config;
	}

	/**
	 * Encodes nmcli connection configuration
	 * @param array<string, mixed> $array Connection configuration
	 * @param string $prefix nmcli configuration prefix
	 * @return string nmcli connection configuration
	 */
	public static function encode(array $array, string $prefix): string {
		$string = '';
		foreach ($array as $key => $value) {
			if (gettype($value) === 'boolean') {
				$value = $value ? 'yes' : 'no';
			}
			if ($value instanceof BackedEnum) {
				$value = $value->value;
			}
			$string .= sprintf('%s.%s "%s" ', $prefix, $key, $value);
		}
		return $string;
	}

}
