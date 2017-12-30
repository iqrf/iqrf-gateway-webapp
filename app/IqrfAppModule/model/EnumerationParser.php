<?php

/**
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
 * Parser for DPA Enumeration responses.
 */
class EnumerationParser {

	use Nette\SmartObject;

	/**
	 * Parse DPA Coordinator response
	 * @param string $packet DPA packet
	 * @return array Parsed data
	 */
	public function parse(string $packet) {
		$pcmd = explode('.', $packet)[3];
		switch ($pcmd) {
			case 'BF':
				return $this->parsePeripheralEnumeration($packet);
		}
	}

	/**
	 * Parse response to DPA Peripheral enumeration request
	 * @param string $packet DPA packet response
	 * @return array Parsed peripheral enumeration
	 * @todo Add parser for User's peripherals
	 */
	public function parsePeripheralEnumeration(string $packet): array {
		$data = [];
		$rfModes = ['01' => 'STD', '02' => 'LP'];
		$packetArray = explode('.', $packet);
		$data['DpaVer'] = hexdec($packetArray[9]) . '.' . $packetArray[8];
		$data['PerNr'] = hexdec($packetArray[10]);
		$data['EmbeddedPers'] = $this->getEmbeddedPers($packet);
		$data['HWPID'] = $packetArray[16] . '.' . $packetArray[15];
		$data['HWPIDver'] = hexdec($packetArray[18]) . '.' . $packetArray[17];
		$data['RfMode'] = $rfModes[$packetArray[19]];
		//$data['UserPer'];
		return $data;
	}

	/**
	 * Get Embedded peripherals from DPA response
	 * @param string $packet DPA packet response
	 * @return array Embedded Peripherals
	 */
	public function getEmbeddedPers(string $packet): array {
		$data = [];
		$peripherals = [
			1 => 'NODE', 2 => 'OS', 3 => 'EEPROM', 4 => 'EEEPROM', 5 => 'RAM',
			6 => 'LEDR', 7 => 'LEDG', 9 => 'IO', 10 => 'Thermometer',
		];
		$array = explode('.', $packet);
		$part = $array[11] . $array[12] . $array[13] . $array[14] . $array[15];
		$bits = array_keys(str_split(str_pad(strrev(base_convert($part, 16, 2)), 16, '0')));
		foreach ($bits as $bit) {
			if (array_key_exists($bit, $peripherals)) {
				$data[] = $peripherals[$bit];
			}
		}
		return $data;
	}

}
