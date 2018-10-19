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

namespace App\IqrfNetModule\Requests;

use Nette\Utils\Strings;

/**
 * JSON DPA request
 */
class DpaRequest extends ApiRequest {

	/**
	 * Fix raw DPA packet
	 */
	private function fixRawPacket(): void {
		$packet = &$this->request['data']['req']['rData'];
		$data = explode('.', trim($packet, '.'));
		$nadrLo = $data[0];
		$nadrHi = $data[1];
		if ($nadrHi !== '00' && $nadrLo === '00') {
			$data[1] = $nadrLo;
			$data[0] = $nadrHi;
		}
		$packet = Strings::lower(implode('.', $data));
	}

	/**
	 * Fix DPA requests
	 */
	private function fixRequest() {
		$mType = $this->request['mType'] ?? 'unknown';
		switch ($mType) {
			case 'iqrfRaw':
				$this->fixRawPacket();
				break;
		}
	}

	/**
	 * Set JSON DPA request
	 * @param array $request JSON DPA request
	 */
	public function setRequest(array $request): void {
		parent::setRequest($request);
		$this->fixRequest();
	}

}
