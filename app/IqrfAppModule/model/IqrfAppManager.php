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

namespace App\IqrfAppModule\Model;

use App\IqrfAppModule\Model\CoordinatorParser;
use App\IqrfAppModule\Model\OsParser;
use App\Model\CommandManager;
use Nette;

class IqrfAppManager {

	use Nette\SmartObject;

	/**
	 * @var CommandManager
	 */
	private $commandManager;

	/**
	 * @var CoordinatorParser
	 */
	private $coordinatorParser;

	/**
	 * @var OsParser
	 */
	private $osParser;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command Manager
	 * @param CoordinatorParser $coordinatorParser
	 * @param OsParser $osParser
	 */
	public function __construct(CommandManager $commandManager, CoordinatorParser $coordinatorParser, OsParser $osParser) {
		$this->commandManager = $commandManager;
		$this->coordinatorParser = $coordinatorParser;
		$this->osParser = $osParser;
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
		if (empty($packet) || count($output) === 2) {
			return null;
			// throw new Exception();
		}
		$pnum = explode('.', $packet)[2];
		switch ($pnum) {
			case '00':
				return $this->coordinatorParser->parse($packet);
			case '02':
				return $this->osParser->parse($packet);
			default:
				return null;
		}
	}

}
