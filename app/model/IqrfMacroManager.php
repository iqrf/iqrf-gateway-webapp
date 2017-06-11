<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Model;

use Nette;
use Tracy\Debugger;

class IqrfMacroManager {

	use Nette\SmartObject;

	/**
	 * @var string
	 * @inject
	 */
	private $path;

	/**
	 * Constructor
	 * @param string $path Path to IQRF IDE MAcro file
	 */
	public function __construct($path) {
		$this->path = $path;
	}

	public function read() {
		$config = parse_ini_file($this->path, true)['Macro'];
		$array = $this->parseMacros($config['Macros']);
		return $array;
	}

	public function parseMacros($base16) {
		$string = $this->hex2ascii($base16);
		$array = explode("\r\n", $string);
		for ($i = 0; $i < 3; $i++) {
			array_shift($array);
		}
		return $this->parseMacro($array);
	}

	public function parseMacro($array) {
		$macros = [];
		$keys = array_keys($array);
		for ($i = 0; $i < end($keys); $i++) {
			$id = floor($i / 63);
			switch ($i % 63) {
				case 0:
					$macros[$id]['Name'] = $array[$i];
					break;
				case 1:
					$macros[$id]['Enabled'] = $array[$i];
					break;
				case 2:
					break;
				default:
					$macroId = (floor(($i - 3) / 5)) % 12;
					switch ((($i - 3) - (63 * $id)) % 5) {
						case 0:
							$macros[$id]['Macros'][$macroId]['Name'] = $array[$i];
							break;
						case 1:
							$macros[$id]['Macros'][$macroId]['Packet'] = $array[$i];
							break;
						case 2:
							$macros[$id]['Macros'][$macroId]['Enabled'] = $array[$i] === 'True' ? true : false;
							break;
					}
			}
		}
		return $macros;
	}

	public function hex2ascii($hex) {
		$string = '';
		for ($i = 0; $i < strlen($hex); $i += 2) {
			$string .= chr(hexdec(substr($hex, $i, 2)));
		}
		return $string;
	}

}
