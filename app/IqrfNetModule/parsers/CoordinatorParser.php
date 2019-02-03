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

namespace App\IqrfNetModule\Parsers;

use Nette\SmartObject;
use Nette\Utils\Strings;

/**
 * Parsers for DPA Coordinator responses.
 */
class CoordinatorParser implements IParser {

	use SmartObject;

	/**
	 * Parse DPA Coordinator response
	 * @param string $packet DPA packet
	 * @return mixed[]|null Parsed data
	 */
	public function parse(string $packet): ?array {
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
		return null;
	}

	/**
	 * Parse response to DPA Coordinator - "Get bonded nodes" and "Get discovered nodes" request
	 * @param string $packet DPA packet response
	 * @return mixed[] Bonded XOR discovered nodes
	 */
	public function parseGetNodes(string $packet): array {
		$packetArray = explode('.', $packet);
		$pcmd = $packetArray[3];
		switch ($pcmd) {
			case '81':
				$type = 'DiscoveredNodes';
				break;
			case '82':
				$type = 'BondedNodes';
				break;
			default:
				$type = 'UnknownType';
		}
		return [$type => $this->bitmapToStatuses($packetArray)];
	}

	/**
	 * Convert bitmap to statuses
	 * @param mixed[] $packet DPA response
	 * @return mixed[][] Nodes
	 */
	private function bitmapToStatuses(array $packet): array {
		$bitMap = array_slice($packet, 8);
		$result = [];
		$tempArray = [];
		for ($i = 0; $i < 30; $i++) {
			$tempArray[] = Strings::reverse(Strings::padLeft(base_convert($bitMap[$i], 16, 2), 8, '0'));
		}
		$temp = str_split(implode('', $tempArray));
		foreach ($temp as $key => $char) {
			$result[intdiv($key, 10)][$key % 10] = boolval($char);
		}
		$result[0][0] = null;
		return $result;
	}

	/**
	 * Parse response to DPA Coordinator - "Bond node" request
	 * @param string $packet DPA packet response
	 * @return mixed[] Bonded node
	 */
	public function parseBondNode(string $packet): array {
		$data = [];
		$packetArray = explode('.', $packet);
		$data['bondAddr'] = $packetArray[8];
		$data['devNr'] = $packetArray[9];
		return $data;
	}

}
