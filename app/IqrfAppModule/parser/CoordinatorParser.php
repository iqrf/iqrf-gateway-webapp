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

namespace App\IqrfAppModule\Parser;

use Nette;

/**
 * Parser for DPA Coordinator responses.
 */
class CoordinatorParser implements IParser {

	use Nette\SmartObject;

	/**
	 * Parse DPA Coordinator response
	 * @param string $packet DPA packet
	 * @return array|null Parsed data
	 */
	public function parse(string $packet) {
		$data = explode('.', $packet);
		$pnum = $data[2];
		if ($pnum !== '00') {
			return null;
		}
		$pcmd = $data[3];
		switch ($pcmd) {
			case '81':
				return $this->parseGetNodes($packet);
			case '82':
				return $this->parseGetNodes($packet);
			case '84':
				return $this->parseBondNode($packet);
		}
	}

	/**
	 * Parse response to DPA Coordinator - "Bond node" request
	 * @param string $packet DPA packet response
	 * @return array Bonded node
	 */
	public function parseBondNode(string $packet): array {
		$data = [];
		$packetArray = explode('.', $packet);
		$data['bondAddr'] = $packetArray[8];
		$data['devNr'] = $packetArray[9];
		return $data;
	}

	/**
	 * Parse response to DPA Coordinator - "Get bonded nodes" and "Get discovered nodes" request
	 * @param string $packet DPA packet response
	 * @return array Bonded XOR discovered nodes
	 */
	public function parseGetNodes(string $packet): array {
		$data = [];
		$packetArray = explode('.', $packet);
		$pcmd = $packetArray[3];
		$type = 'UnknownType';
		if ($pcmd === '81') {
			$type = 'DiscoveredNodes';
		} elseif ($pcmd === '82') {
			$type = 'BondedNodes';
		}
		for ($i = 0; $i < 30; $i += 2) {
			$data[$type][$i / 2] = str_split(str_pad(strrev(base_convert($packetArray[9 + $i] . $packetArray[8 + $i], 16, 2)), 16, '0'));
		}
		return $data;
	}

}
