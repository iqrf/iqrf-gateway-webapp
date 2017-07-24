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

use App\Model\CommandManager;
use Nette;

class IqrfAppManager {

	use Nette\SmartObject;

	/**
	 * @var CommandManager
	 * @inject
	 */
	private $commandManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command Manager
	 */
	public function __construct(CommandManager $commandManager) {
		$this->commandManager = $commandManager;
	}

	/**
	 * Send RAW IQRF packet
	 * @param string $packet RAW IQRF packet
	 */
	public function sendRaw($packet) {
		$cmd = 'iqrfapp raw ' . $packet;
		return $this->commandManager->send($cmd, true);
	}

	/**
	 * Change iqrf-daemon operation mode
	 * @param string $mode iqrf-daemon operation mode
	 * @return string
	 */
	public function changeOperationMode($mode) {
		$modes = ['forwarding', 'operational', 'service'];
		if (in_array($mode, $modes, true)) {
			$cmd = 'iqrfapp "{\"ctype\": \"conf\",\"type\": \"mode\",\"cmd\": \"' . $mode . '\"}"';
			return $this->commandManager->send($cmd, true);
		}
	}

	/**
	 * Validate raw IQRF packet
	 * @param string $packet Raw IQRF packet
	 * @return bool Status
	 */
	public function validatePacket($packet) {
		$pattern = '/^([0-9a-fA-F]{1,2}(\.|\ )){1,64}[0-9a-fA-F]{1,2}(\.|)$/';
		return (bool) preg_match($pattern, $packet);
	}

	/**
	 * Parse DPA response
	 * @param string $response DPA packet response
	 * @return array Parsed response in array
	 * @throws Exception
	 */
	public function parseResponse($response) {
		$output = explode(' ', $response);
		$status = end($output);
		if ($status !== 'STATUS_NO_ERROR') {
			return null;
			// throw new Exception();
		}
		$packet = $output[1];
		if (empty($packet)) {
			return null;
			// throw new Exception();
		}
		$data = explode('.', $packet);
		$pnum = $data[2];
		$pcmd = $data[3];
		if ($pnum == '00' && ($pcmd == '81' || $pcmd == '82')) {
			return $this->parseCoordinatorGetNodes($packet);
		}
		if ($pnum == '02' && $pcmd == '80') {
			return $this->parseOsReadInfo($packet);
		}
		return null;
	}

	/**
	 * Parse response to DPA Coordinator - "Get bonded nodes" and "Get discovered nodes" request
	 * @param string $packet DPA packet response
	 * @return Information about DCTR module
	 */
	public function parseCoordinatorGetNodes($packet) {
		$data = [];
		$packetArray = explode('.', $packet);
		if ($packetArray[3] === '81') {
			$type = 'DiscoveredNodes';
		} elseif ($packetArray[3] === '82') {
			$type = 'BondedNodes';
		}
		for ($i = 0; $i < 24; $i += 2) {
			$data[$type][$i / 2] = str_split(str_pad(strrev(base_convert($packetArray[9 + $i] . $packetArray[8 + $i], 16, 2)), 20, "0"));
		}
		return $data;
	}

	/**
	 * Parse response to DPA OS - "Read info" request
	 * @param string $packet DPA packet response
	 * @return Information about DCTR module
	 */
	public function parseOsReadInfo($packet) {
		$data = [];
		$packetArray = explode('.', $packet);
		$data['ModuleId'] = strtoupper($packetArray[11] . $packetArray[10] . $packetArray[9] . $packetArray[8]);
		$data['OsVersion'] = (hexdec($packetArray[12]) >> 4) . '.0' . (hexdec($packetArray[12]) & 0x0f) . 'D';
		$data['TrType'] = (hexdec($packetArray[11]) & 0x80) ? 'DCTR-' : 'TR-';
		switch (hexdec($packetArray[13]) >> 4) {
			case 0:
				$data['TrType'] .= '52D';
				break;
			case 1:
				$data['TrType'] .= '58D-RJ';
				break;
			case 2:
				$data['TrType'] .= '72D';
				break;
			case 3:
				$data['TrType'] .= '53D';
				break;
			case 8:
				$data['TrType'] .= '54D';
				break;
			case 9:
				$data['TrType'] .= '55D';
				break;
			case 10:
				$data['TrType'] .= '56D';
				break;
			case 11:
				$data['TrType'] .= '76D';
				break;
			default:
				$data['TrType'] = 'UNKNOWN';
		}
		switch (hexdec($packetArray[13]) & 7) {
			case 3:
				$data['McuType'] = 'PIC16F886';
				break;
			case 4:
				$data['McuType'] = 'PIC16F1938';
				break;
			default:
				$data['McuType'] = 'UNKNOWN';
		}
		$data['OsBuild'] = $packetArray[14] . $packetArray[15];
		$data['Rssi'] = $packetArray[16];
		$data['SupplyVoltage'] = number_format((261.12 / (127 - hexdec($packetArray[17]))), 2, '.', '') . ' V';
		$data['Flags'] = $packetArray[18];
		$data['SlotLimits'] = $packetArray[19];
		return $data;
	}

}
