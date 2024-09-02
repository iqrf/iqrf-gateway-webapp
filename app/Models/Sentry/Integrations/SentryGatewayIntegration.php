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

namespace App\Models\Sentry\Integrations;

use App\GatewayModule\Models\Utils\GatewayInfoUtil;
use Contributte\Sentry\Integration\BaseIntegration;
use Nette\Utils\Strings;
use Sentry\Event;
use Sentry\EventHint;
use Sentry\State\HubInterface;

class SentryGatewayIntegration extends BaseIntegration {

	/**
	 * Constructor
	 * @param GatewayInfoUtil $infoManager Info manager
	 */
	public function __construct(
		private readonly GatewayInfoUtil $infoManager,
	) {
	}

	/**
	 * Sets up Sentry event Gateway integration
	 * @param HubInterface $hub Sentry hub
	 * @param Event $event Sentry event
	 * @param EventHint $hint Event hint
	 * @return Event|null Returns Sentry event
	 */
	public function setup(HubInterface $hub, Event $event, EventHint $hint): ?Event {
		$gwId = Strings::lower($this->infoManager->getId());
		if ($gwId === 'ffffffffffffffff') {
			return $event;
		}
		$event->setTag('gateway.id', $gwId);
		$event->setTag('gateway.manufacturer', $this->infoManager->getManufacturer());
		$event->setTag('gateway.product', $this->infoManager->getProduct());
		$event->setTag('gateway.host', $this->infoManager->getHost());
		$event->setTag('gateway.image', $this->infoManager->getImage());
		$event->setTag('gateway.interface', $this->infoManager->getInterface());
		return $event;
	}

}
