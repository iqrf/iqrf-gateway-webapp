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

namespace App\Models\Mail\Senders\TemplateParameters;

use App\GatewayModule\Models\InfoManager;
use App\Models\Database\Entities\User;

/**
 * Layout parameters
 */
readonly class LayoutParameters {

	/**
	 * Constructor
	 * @param InfoManager $gatewayInfo Gateway information manager
	 * @param string $locale Locale
	 * @param ?User $userInfo Recipient of the notification
	 */
	public function __construct(
		protected InfoManager $gatewayInfo,
		public string $locale,
		public User|null $userInfo = null,
	) {
	}

	/**
	 * Converts the template parameters to an array
	 * @return array{
	 *     gatewayInfo: InfoManager,
	 *     locale: string,
	 *     userEntity: User|null,
	 * } Template parameters
	 */
	public function toArray(): array {
		return [
			'gatewayInfo' => $this->gatewayInfo,
			'locale' => $this->locale,
			'userEntity' => $this->userInfo,
		];
	}

}
