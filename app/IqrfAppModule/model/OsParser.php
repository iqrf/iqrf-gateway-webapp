<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017 IQRF Tech s.r.o.
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

declare(strict_types=1);

namespace App\IqrfAppModule\Model;

use Nette;

/**
 * Parser for DPA OS responses
 */
class OsParser {

	use Nette\SmartObject;

	/**
	 * Parse DPA OS response
	 * @param string $packet DPA packet
	 * @return array
	 */
	public function parse(string $packet) {
		$data = explode('.', $packet);
		$pcmd = $data[3];
		switch ($pcmd) {
			case '80':
				return $this->parseReadInfo($packet);
		}
	}

	/**
	 * Parse response to DPA OS - "Read info" request
	 * @param string $packet DPA packet response
	 * @return array Information about DCTR module
	 */
	public function parseReadInfo(string $packet) {
		$data = [];
		$trTypes = [0 => '52D', 1 => '58D-RJ', 2 => '72D', 3 => '53D', 8 => '54D', 9 => '55D', 10 => '56D', 11 => '76D'];
		$mcuTypes = [3 => 'PIC16F886', 4 => 'PIC16F1938'];
		$packetArray = explode('.', $packet);
		$data['ModuleId'] = strtoupper($packetArray[11] . $packetArray[10] . $packetArray[9] . $packetArray[8]);
		$data['OsVersion'] = (hexdec($packetArray[12]) >> 4) . '.0' . (hexdec($packetArray[12]) & 0x0f) . 'D';
		$trType = hexdec($packetArray[13]) >> 4;
		if (array_key_exists($trType, $trTypes)) {
			$data['TrType'] = ((hexdec($packetArray[11]) & 0x80) ? 'DCTR-' : 'TR-') . $trTypes[$trType];
		} else {
			$data['TrType'] = 'UNKNOWN';
		}
		$mcuType = hexdec($packetArray[13]) & 7;
		$data['McuType'] = array_key_exists($mcuType, $mcuTypes) ? $mcuTypes[$mcuType] : 'UNKNOWN';
		$data['OsBuild'] = strtoupper($packetArray[15] . $packetArray[14]);
		$data['Rssi'] = hexdec($packetArray[16]) - 130;
		$data['SupplyVoltage'] = number_format((261.12 / (127 - hexdec($packetArray[17]))), 2, '.', '') . ' V';
		$data['Flags'] = $packetArray[18];
		$data['SlotLimits'] = $packetArray[19];
		return $data;
	}

}
