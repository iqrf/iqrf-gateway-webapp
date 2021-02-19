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

namespace App\NetworkModule\Utils;

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
	 * @param string $prefix nmcli configuration prefix
	 * @return array<string, string|array<string>> Connection configuration
	 */
	public static function decode(string $data, string $prefix): array {
		$config = [];
		$array = explode(PHP_EOL, Strings::trim($data));
		foreach ($array as $i => $row) {
			$temp = explode(':', $row, 2);
			if (!Strings::startsWith($temp[0], $prefix . '.')) {
				continue;
			}
			$key = Strings::lower(Strings::replace($temp[0], '~' . $prefix . '\.~', ''));
			if (Strings::contains($key, '[')) {
				[$key, $idx] = Strings::split($key, '/\[(\d)\]/');
				$config[$key][$idx] = $temp[1];
			} else {
				$config[$key] = $temp[1];
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
			$string .= sprintf('%s.%s "%s" ', $prefix, $key, $value);
		}
		return $string;
	}

}
