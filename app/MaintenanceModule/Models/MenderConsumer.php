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

namespace App\MaintenanceModule\Models;

use App\CoreModule\Models\WebSocketClient;
use App\MaintenanceModule\Enums\MenderActions;
use Bunny\Message;
use Contributte\RabbitMQ\Consumer\IConsumer;

/**
 * Mender MQ consumer
 */
final class MenderConsumer implements IConsumer {

	/**
	 * Mender MQ WebSocket server endpoint TODO: from config
	 */
	private const ENDPOINT = 'ws://localhost:8082/mender';

	/**
	 * @var MenderManager Mender manager
	 */
	private $menderManager;

	/**
	 * @var WebSocketClient WebSocket client
	 */
	private $wsClient;

	/**
	 * Constructor
	 */
	public function __construct(MenderManager $menderManager) {
		$this->menderManager = $menderManager;
		$this->wsClient = new WebSocketClient(self::ENDPOINT);
	}

	/**
	 * Consumes a Mender MQ message and sends it to WebSocket server
	 * @param Message $message MQ message
	 */
	public function consume(Message $message): int {
		$headers = $message->headers;
		$action = $headers['action'];
		switch ($action) {
			case MenderActions::INSTALL():
				$this->menderManager->installArtifact($message->content);
				break;
			case MenderActions::COMMIT():
				$this->menderManager->commitUpdate();
				break;
			case MenderActions::ROLLBACK():
				$this->menderManager->rollbackUpdate();
				break;
			case MenderActions::RESULT():
				$this->wsClient->send($message->content);
				break;
			default:
				return IConsumer::MESSAGE_REJECT;
		}
		return IConsumer::MESSAGE_ACK;
	}

}
