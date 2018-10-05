<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
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

namespace App\IqrfAppModule\Model;

use Nette\SmartObject;

/**
 * Tool for parsing IQRF IDE macros.
 */
class IqrfMacroManager {

	use SmartObject;

	/**
	 * @var string Path to IQRF IDE Macro file
	 */
	private $path;

	/**
	 * Constructor
	 * @param string $path Path to IQRF IDE Macro file
	 */
	public function __construct(string $path) {
		$this->path = $path;
	}

	/**
	 * Read .ini file and parse macros from IQRF IDE
	 * @return array
	 */
	public function read(): array {
		$config = parse_ini_file($this->path, true)['Macro'];
		$array = $this->parseMacros($config['Macros']);
		return $array;
	}

	/**
	 * Parse data from HEX to array
	 * @param string $hex Data in HEX
	 * @return array Data in array
	 */
	public function parseMacros(string $hex): array {
		$array = explode("\r\n", trim($this->hex2ascii($hex)));
		for ($i = 0; $i < 3; $i++) {
			array_shift($array);
		}
		$macros = [];
		foreach ($array as $key => $value) {
			$category = $key % 63;
			$categoryId = intval(floor($key / 63));
			$macro = (($key - ($categoryId * 63)) - 2) % 5;
			$macroId = floor(($key - 3 - ($categoryId * 63)) / 5);
			if ($category === 0) {
				$macros[$categoryId]['Name'] = $value;
			} else {
				if ($category === 1) {
					$macros[$categoryId]['Enabled'] = $value === 'True';
				} else {
					if ($macro === 1) {
						$macros[$categoryId]['Macros'][$macroId]['Name'] = $value;
					} else {
						if ($macro === 2) {
							$macros[$categoryId]['Macros'][$macroId]['Packet'] = trim($value, '.');
						} else {
							if ($macro === 3) {
								$macros[$categoryId]['Macros'][$macroId]['Enabled'] = $value === 'True';
							}
						}
					}
				}
			}
		}
		return $macros;
	}

	/**
	 * Convert data from HEX to ASCII
	 * @param string $hex Data in HEX
	 * @return string Data in ASCII
	 */
	public function hex2ascii(string $hex): string {
		$string = '';
		for ($i = 0; $i < strlen($hex); $i += 2) {
			$string .= chr(hexdec(substr($hex, $i, 2)));
		}
		return $string;
	}

}
