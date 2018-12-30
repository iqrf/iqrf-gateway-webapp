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

namespace App\IqrfNetModule\Parsers;

use Nette\SmartObject;

/**
 * Parsers for DPA OS responses
 */
class OsParser implements IParser {

	use SmartObject;

	/**
	 * Parse DPA OS response
	 * @param string $packet DPA packet
	 * @return mixed[]|null Parsed data
	 */
	public function parse(string $packet): ?array {
		$data = explode('.', $packet);
		$pnum = $data[2];
		if ($pnum !== '02') {
			return null;
		}
		$pcmd = $data[3];
		switch ($pcmd) {
			case '80':
				return $this->parseReadInfo($packet);
			case '82':
				return $this->parseHwpConfiguration($packet);
		}
		return null;
	}

	/**
	 * Parse response to DPA OS - "Read info" request
	 * @param string $packet DPA packet response
	 * @return mixed[] Information about DCTR module
	 */
	public function parseReadInfo(string $packet): array {
		$data = [];
		$trTypes = [0 => '52D', 1 => '58D-RJ', 2 => '72D', 3 => '53D', 4 => '78D', 8 => '54D', 9 => '55D', 10 => '56D', 11 => '76D', 12 => '77D', 13 => '75D'];
		$mcuTypes = [3 => 'PIC16F886', 4 => 'PIC16F1938'];
		$packetArray = explode('.', $packet);
		$data['ModuleId'] = strtoupper($packetArray[11] . $packetArray[10] . $packetArray[9] . $packetArray[8]);
		$data['OsVersion'] = (hexdec($packetArray[12]) >> 4) . '.0' . (hexdec($packetArray[12]) & 0x0f) . 'D';
		$trType = hexdec($packetArray[13]) >> 4;
		if (array_key_exists($trType, $trTypes)) {
			$data['TrType'] = ((hexdec($packetArray[11]) & 0x80) !== 0 ? 'DCTR-' : 'TR-') . $trTypes[$trType];
		} else {
			$data['TrType'] = 'UNKNOWN';
		}
		$mcuType = hexdec($packetArray[13]) & 7;
		$data['McuType'] = array_key_exists($mcuType, $mcuTypes) ? $mcuTypes[$mcuType] : 'UNKNOWN';
		$data['OsBuild'] = strtoupper($packetArray[15] . $packetArray[14]);
		$data['Rssi'] = (hexdec($packetArray[16]) - 130) . ' dBm';
		$data['SupplyVoltage'] = number_format((261.12 / (127 - hexdec($packetArray[17]))), 2, '.', '') . ' V';
		$data['Flags'] = $packetArray[18];
		$data['SlotLimits'] = $packetArray[19];
		return $data;
	}

	/**
	 * Parse response to DPA OS - "Read HWP configuration" request
	 * @param string $packet DPA packet response
	 * @return mixed[] HWP configuration
	 */
	public function parseHwpConfiguration(string $packet): array {
		$data = [];
		$packetArray = explode('.', $packet);
		$data['checksum'] = $packetArray[8];
		$config = array_slice($packetArray, 9, 31);
		$data['configuration'] = $config;
		$data['parsedConfiguration'] = $this->parseTrConfiguration($config);
		$data['rfpgm'] = $packetArray[40];
		$data['rfBand'] = $this->getRfBand($packetArray[41]);
		return $data;
	}

	/**
	 * Parse TR configuration
	 * @param mixed[] $config HWP configuration
	 * @return mixed[] TR configuration
	 */
	public function parseTrConfiguration(array $config): array {
		$data = [];
		$configFixed = [];
		$baudRates = [1200, 2400, 4800, 9600, 19200, 38400, 57600, 115200, 230400];
		foreach ($config as $key => $value) {
			$configFixed[$key] = hexdec($value) ^ 0x34;
		}
		$data['checksum'] = dechex($configFixed[0]);
		$data['mainChannelA'] = $configFixed[16];
		$data['mainChannelB'] = $configFixed[17];
		$data['secondChannelA'] = $configFixed[5];
		$data['secondChannelB'] = $configFixed[6];
		$data['rfOutputPower'] = $configFixed[7];
		$data['rxSignalFilter'] = $configFixed[8];
		$data['rfLpTimeout'] = $configFixed[9];
		$data['baudRate'] = $baudRates[$configFixed[10]];
		return $data;
	}

	/**
	 * Get RF band from HWP configuration
	 * @param string $byte Undocumented byte from HWP configuration
	 * @return string RF band
	 */
	public function getRfBand(string $byte): string {
		$bands = ['868 MHz', '916 MHz', '433 MHz'];
		$bit = intval(base_convert($byte, 16, 2)) & 0x3;
		return $bands[$bit];
	}

}
