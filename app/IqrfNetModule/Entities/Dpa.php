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

namespace App\IqrfNetModule\Entities;

use App\IqrfNetModule\Enums\DpaInterfaces;
use App\IqrfNetModule\Enums\RfModes;
use App\IqrfNetModule\Enums\TrSeries;

/**
 * DPA entity
 */
class Dpa {

	/**
	 * @var string DPA version
	 */
	private string $version;

	/**
	 * @var DpaInterfaces DPA communication interface
	 */
	private DpaInterfaces $interface;

	/**
	 * @var TrSeries TR series
	 */
	private TrSeries $trSeries;

	/**
	 * @var RfModes|null RF mode
	 */
	private ?RfModes $rfMode;

	/**
	 * Constructor
	 * @param string $version DPA version
	 * @param DpaInterfaces $interface DPA communication interface
	 * @param TrSeries $trSeries TR series
	 * @param RfModes|null $rfMode RF mode
	 */
	public function __construct(string $version, DpaInterfaces $interface, TrSeries $trSeries, ?RfModes $rfMode) {
		$this->version = $version;
		$this->interface = $interface;
		$this->trSeries = $trSeries;
		$this->rfMode = $rfMode;
	}

	/**
	 * Returns DPA file prefixes
	 * @return array<string> DPA file prefixes
	 */
	public function getFilePrefixes(): array {
		$interface = $this->interface->toScalar();
		$trSeries = $this->trSeries->toScalar();
		$filePrefixes = [];
		if ($this->rfMode !== null) {
			$rfMode = $this->rfMode->toScalar();
			$filePrefixes = [
				'GeneralHWP-Coordinator-' . $rfMode . '-' . $interface . '-' . $trSeries . '-',
				'HWP-Coordinator-' . $rfMode . '-' . $interface . '-' . $trSeries . '-',
			];
		}
		$filePrefixes[] = 'DPA-Coordinator-' . $interface . '-' . $trSeries . '-';
		return $filePrefixes;
	}

	/**
	 * Returns DPA version
	 * @return string DPA version
	 */
	public function getVersion(): string {
		return $this->version;
	}

}
