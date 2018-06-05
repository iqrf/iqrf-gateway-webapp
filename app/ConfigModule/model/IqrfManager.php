<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
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

namespace App\ConfigModule\Model;

use App\Model\CommandManager;
use App\Model\JsonFileManager;
use Nette;
use Nette\Utils\ArrayHash;

class IqrfManager {

	use Nette\SmartObject;

	/**
	 * @var CommandManager
	 */
	private $commandManager;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var string File name (without .json)
	 */
	private $fileName = 'IqrfInterface';

	/**
	 * Constructor
	 * @param CommandManager $commandManager
	 * @param JsonFileManager $fileManager JSON file manager
	 */
	public function __construct(CommandManager $commandManager, JsonFileManager $fileManager) {
		$this->commandManager = $commandManager;
		$this->fileManager = $fileManager;
	}

	/**
	 * Load configuration
	 * @return array Array for form
	 */
	public function load(): array {
		return $this->fileManager->read($this->fileName);
	}

	/**
	 * Save configuration
	 * @param ArrayHash $array Settings
	 */
	public function save(ArrayHash $array) {
		$json = $this->fileManager->read($this->fileName);
		$spiKeys = ['enableGpioPin', 'spiCe0GpioPin', 'spiMisoGpioPin', 'spiMosiGpioPin', 'spiClkGpioPin',];
		foreach ($array as $key => $value) {
			if (!isset($value) && in_array($key, $spiKeys)) {
				unset($array[$key]);
			}
		}
		$this->fileManager->write($this->fileName, array_merge($json, (array) $array));
	}

	/**
	 * Create list of SPI and USB CDC interfaces available in the system
	 * @return array SPI and USB CDC interfaces available in the system
	 */
	public function getInterfaces(): array {
		$interfaces = [];
		$interfaces['cdc'] = $this->getCdcInterfaces();
		$interfaces['spi'] = $this->getSpiInterfaces();
		return $interfaces;
	}

	/**
	 * Create list of USB CDC interfaces available in the system
	 * @return array USB CDC interfaces available in the system
	 */
	public function getCdcInterfaces() {
		$cdc = [];
		$ls = $this->commandManager->send("ls /dev/ttyACM* | awk '{ print $0 }'", true);
		foreach (explode(PHP_EOL, $ls) as $path) {
			if (!empty($path)) {
				array_push($cdc, $path);
			}
		}
		if (!empty($cdc)) {
			return $cdc;
		}
	}

	/**
	 * Create list of SPI interfaces available in the system
	 * @return array SPI interfaces available in the system
	 */
	public function getSpiInterfaces() {
		$spi = [];
		$ls = $this->commandManager->send("ls /dev/spidev* | awk '{ print $0 }'", true);
		foreach (explode(PHP_EOL, $ls) as $path) {
			if (!empty($path)) {
				array_push($spi, $path);
			}
		}
		if (!empty($spi)) {
			return $spi;
		}
	}

}
