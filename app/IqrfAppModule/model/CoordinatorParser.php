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

namespace App\IqrfAppModule\Model;

use Nette;

class CoordinatorParser {

	use Nette\SmartObject;

	/**
	 * Parse DPA Coordinator response
	 * @param string $packet DPA packet
	 * @return array
	 */
	public function parse($packet) {
		$pcmd = explode('.', $packet)[3];
		switch ($pcmd) {
			case '81':
				return $this->parseGetNodes($packet);
			case '82':
				return $this->parseGetNodes($packet);
		}
	}

	/**
	 * Parse response to DPA Coordinator - "Get bonded nodes" and "Get discovered nodes" request
	 * @param string $packet DPA packet response
	 * @return array Bonded XOR discovered nodes
	 */
	public function parseGetNodes($packet) {
		$data = [];
		$packetArray = explode('.', $packet);
		$pcmd = $packetArray[3];
		if ($pcmd === '81') {
			$type = 'DiscoveredNodes';
		} elseif ($pcmd === '82') {
			$type = 'BondedNodes';
		}
		for ($i = 0; $i < 24; $i += 2) {
			$data[$type][$i / 2] = str_split(str_pad(strrev(base_convert($packetArray[9 + $i] . $packetArray[8 + $i], 16, 2)), 20, '0'));
		}
		return $data;
	}

}
