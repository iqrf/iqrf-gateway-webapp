<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

namespace App\GatewayModule\Models\Utils;

use App\CoreModule\Models\JsonFileManager;
use Nette\IOException;
use Nette\Schema\Expect;
use Nette\Schema\Processor;
use Nette\Schema\Schema;
use Nette\Utils\JsonException;

class GatewayInfoUtil {

	/**
	 * Gateway file name
	 */
	private const FILE_NAME = 'iqrf-gateway';

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private JsonFileManager $fileManager;

	/**
	 * @var Processor Schema processor
	 */
	private Processor $processor;

	/**
	 * Returns gateway file schema
	 * @return Schema Gateway file schema
	 */
	public function getSchema(): Schema {
		return Expect::structure([
			'gwProduct' => Expect::string('IQD-GW-0X'),
			'gwManufacturer' => Expect::string('MICRORISC s.r.o.'),
			'gwId' => Expect::string('FFFFFFFFFFFFFFFF'),
			'gwToken' => Expect::string('iqube-ffffffffffffffff'),
			'gwHost' => Expect::string('iqube-ffffffffffffffff.local'),
			'gwImage' => Expect::string('iqube-os-vX.Y.Z'),
			'gwInterface' => Expect::string('uart'),
		])->castTo('array');
	}

	/**
	 * Constructor
	 * @param JsonFileManager $fileManager JSON file manager
	 */
	public function __construct(JsonFileManager $fileManager) {
		$this->fileManager = $fileManager;
		$this->processor = new Processor();
	}

	/**
	 * Returns gateway product
	 * @return string Gateway product
	 */
	public function getProduct(): string {
		return $this->read()['gwProduct'];
	}

	/**
	 * Returns gateway manufacturer
	 * @return string Gateway manufacturer
	 */
	public function getManufacturer(): string {
		return $this->read()['gwManufacturer'];
	}

	/**
	 * Returns gateway ID
	 * @return string Gateway ID
	 */
	public function getId(): string {
		return $this->read()['gwId'];
	}

	/**
	 * Returns gateway token
	 * @return string Gateway token
	 */
	public function getToken(): string {
		return $this->read()['gwToken'];
	}

	/**
	 * Returns gateway host
	 * @return string Gateway host
	 */
	public function getHost(): string {
		return $this->read()['gwHost'];
	}

	/**
	 * Returns gateway image
	 * @return string Gateway image
	 */
	public function getImage(): string {
		return $this->read()['gwImage'];
	}

	/**
	 * Returns gateway interface
	 * @return string Gateway interface
	 */
	public function getInterface(): string {
		return $this->read()['gwInterface'];
	}

	/**
	 * Returns gateway configuration if the file exists
	 * @return array<mixed> Gateway configuration
	 */
	public function read(): array {
		try {
			$content = $this->fileManager->read(self::FILE_NAME);
		} catch (IOException | JsonException $e) {
			$content = [];
		}
		return $this->processor->process($this->getSchema(), $content);
	}

}
