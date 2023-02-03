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

namespace App\ApiModule\Version0\Models;

use App\Models\Database\Entities\User;
use Contributte\Sentry\Integration\BaseIntegration;
use Nette\DI\Container;
use Nette\Http\IRequest;
use Sentry\Event;
use Sentry\State\HubInterface;
use Sentry\UserDataBag;

class SentryUserIntegration extends BaseIntegration {

	/**
	 * @var BearerAuthenticator Bearer authenticator
	 */
	protected BearerAuthenticator $authenticator;

	/**
	 * @var Container Nette DI container
	 */
	protected Container $context;

	/**
	 * Constructor
	 * @param Container $container Nette DI container
	 * @param BearerAuthenticator $authenticator Bearer authenticator
	 */
	public function __construct(Container $container, BearerAuthenticator $authenticator) {
		$this->context = $container;
		$this->authenticator = $authenticator;
	}

	public function setup(HubInterface $hub, Event $event): ?Event {
		$httpRequest = $this->context->getByType(IRequest::class, false);

		// There is no http request
		if (!$httpRequest instanceof IRequest) {
			return $event;
		}

		$header = $httpRequest->getHeader('Authorization') ?? '';
		$token = $this->authenticator->parseAuthorizationHeader($header);

		if ($token === null) {
			return $event;
		}

		$user = $this->authenticator->authenticateUser($token);

		if ($user instanceof User) {
			$userDataBag = new UserDataBag();
			$userDataBag->setUsername($user->getUserName());
			$userDataBag->setEmail($user->getEmail());
			$userDataBag->setIpAddress($httpRequest->getRemoteAddress());
			$userDataBag->setMetadata('role', $user->getRole());

			$event->setUser($userDataBag);
		}

		return $event;
	}

}
