<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

namespace App\NetworkModule\Enums;

/**
 * EAP (Extensible Authentication Protocol) phase two authentication method enum
 */
enum EapPhaseTwoMethod: string {

	/// GTC method
	case GTC = 'gtc';
	/// MD5 method
	case MD5 = 'md5';
	/// MSCHAPv2 method
	case MSCHAPV2 = 'mschapv2';

	/**
	 * Serializes EAP phase two authentication method into JSON string
	 * @return string JSON serialized data
	 */
	public function jsonSerialize(): string {
		return $this->value;
	}

}
