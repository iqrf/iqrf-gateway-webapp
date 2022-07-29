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

namespace App\GatewayModule\Models;

use FFI;

class JournalReaderManager {

	/**
	 * @var FFI|null FFI C code interface
	 */
	private ?FFI $ffi = null;

	/**
	 * Contructor
	 */
	public function __construct() {
		$this->ffi = FFI::load(__DIR__ . '/journal.h');
	}

	/**
	 * Returns number of last journal records or records before cursor if specified
	 * @param int $last Number of records to retrieve
	 * @param string|null $cursor Journal cursor
	 * @return array<mixed> Journal records
	 */
	public function getRecords(int $last, ?string $cursor = null): array {
		$this->ffi = $this->ffi;
		return [];
	}

}
