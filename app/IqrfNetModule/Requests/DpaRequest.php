<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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
	 * Fixes a DPA packet
	 */
	private function fixRawPacket(): void {
		if (is_array($this->request)) {
			$packet = &$this->request['data']['req']['rData'];
		} else {
			$packet = &$this->request->data->req->rData;
		}
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
	 * Fixes DPA requests
	 */
	private function fixRequest(): void {
		if (is_array($this->request)) {
			$mType = $this->request['mType'];
		} else {
			$mType = $this->request->mType;
		}
		switch ($mType) {
			case 'iqrfRaw':
				$this->fixRawPacket();
				break;
		}
	}

	/**
	 * Sets the JSON DPA request
	 * @param mixed $request JSON DPA request
	 */
	public function setRequest($request): void {
		parent::setRequest($request);
		$this->fixRequest();
	}

}
