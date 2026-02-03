<?php

/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
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

namespace Tests\Toolkit\Traits;

use Ratchet\Client\Connector;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use Throwable;

/**
 * WebSocket client connector trait
 */
trait TWebSocketClientConnector {

	/**
	 * @var LoopInterface|null Event loop
	 */
	protected ?LoopInterface $loop;

	/**
	 * Creates and returns WebSocket client connector
	 * @return Connector WebSocket client connector
	 */
	protected function createConnector(): Connector {
		$this->loop = Loop::get();
		return new Connector($this->loop);
	}

	/**
	 * Cleans up connector loop
	 */
	protected function cleanupConnector(): void {
		if ($this->loop !== null) {
			try {
				$this->loop->stop();
			} catch (Throwable) {
				// does not matter
			}
			$this->loop = null;
		}
	}

}
