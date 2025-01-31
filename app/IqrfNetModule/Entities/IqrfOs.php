<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

use App\IqrfNetModule\Enums\TrSeries;
use Nette\Utils\Strings;

/**
 * IQRF OS entity
 */
class IqrfOs {

	/**
	 * Constructor
	 * @param string $build IQRF OS build
	 * @param string $version IQRF OS version
	 * @param TrSeries|null $trSeries IQRF TR series
	 */
	public function __construct(
		private readonly string $build,
		private readonly string $version,
		private readonly ?TrSeries $trSeries = null,
	) {
	}

	/**
	 * Creates a new IQRF OS entity from DPA OS Read response
	 * @param array<mixed> $api API request and response
	 * @return IqrfOs IQRF OS entity
	 */
	public static function fromOsRead(array $api): self {
		$data = $api['response']->data->rsp->result;
		$build = Strings::upper(Strings::padLeft(dechex($data->osBuild), 4, '0'));
		$versionHi = dechex($data->osVersion >> 4);
		$versionLo = Strings::padLeft(dechex($data->osVersion & 0xf), 2, '0');
		$version = Strings::upper($versionHi . $versionLo);
		$trSeries = TrSeries::fromTrMcuType($data->trMcuType);
		return new self($build, $version, $trSeries);
	}

	/**
	 * Returns IQRF OS build
	 * @return string IQRF OS build
	 */
	public function getBuild(): string {
		return $this->build;
	}

	/**
	 * Returns IQRF OS description
	 * @return string IQRF OS description
	 */
	public function getDescription(): string {
		return 'IQRF OS ' . $this->getVersion(true) . ' (' . $this->build . ')';
	}

	/**
	 * Returns IQRF TR series
	 * @return TrSeries|null IQRF TR series
	 */
	public function getTrSeries(): ?TrSeries {
		return $this->trSeries;
	}

	/**
	 * Returns IQRF OS version
	 * @param bool $pretty Pretty output?
	 * @return string IQRF OS version
	 */
	public function getVersion(bool $pretty = false): string {
		if (!$pretty) {
			return $this->version;
		}
		$int = hexdec($this->version);
		$version = $int >> 8 . '.';
		return $version . Strings::padLeft(dechex($int & 0xff), 2, '0') . 'D';
	}

}
