<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

use Nette\IOException;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;
use Nette\Schema\Processor;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

class GatewayInfoUtil {

	/**
	 * @var string Gateway file path
	 */
	private string $path;

	/**
	 * Returns gateway file schema
	 * @return Structure Gateway file schema
	 */
	public function getSchema(): Structure {
		return Expect::structure([
			'gwProduct' => Expect::string('IQD-GW-0X'),
			'gwManufacturer' => Expect::string('MICRORISC s.r.o.'),
			'gwId' => Expect::string('FFFFFFFFFFFFFFFF'),
			'gwHost' => Expect::string('iqube-ffffffffffffffff.local'),
			'gwImage' => Expect::string('iqube-os-vX.Y.Z'),
			'gwInterface' => Expect::string('unknown'),
		])->castTo('array');
	}

	/**
	 * Constructor
	 * @param string $path Gateway file path
	 */
	public function __construct(string $path) {
		$this->path = $path;
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
			$content = Json::decode(FileSystem::read($this->path));
		} catch (IOException | JsonException $e) {
			$content = [];
		}
		$processor = new Processor();
		return $processor->process($this->getSchema(), $content);
	}

}
