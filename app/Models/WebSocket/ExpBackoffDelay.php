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

namespace App\Models\WebSocket;

use Random\Randomizer;

/**
 * Exponential backoff with jitter delay utility class
 */
class ExpBackoffDelay {

	/**
	 * Base delay value
	 */
	private const BASE_DELAY = 2;

	/**
	 * Milliseconds
	 */
	private const MILLIS = 1000;

	/**
	 * @var int Counter
	 */
	private int $counter = 0;

	/**
	 * @var bool Maximum reached
	 */
	private bool $maxReached = false;

	/**
	 * @var Randomizer Randomizer
	 */
	private Randomizer $randomizer;

	/**
	 * Constructs an exponential backoff delay
	 *
	 * @param int $maxDelay Maximum backoff delay
	 */
	public function __construct(
		private readonly int $maxDelay,
	) {
		$this->randomizer = new Randomizer();
	}

	/**
	 * Returns next backoff delay
	 *
	 * The delay is calculated with jitter to spread out client reconnections.
	 * The jitter starts at 25% goes down with each attempt, to 10% at maximum delay.
	 *
	 * @return float Backoff delay
	 */
	public function getNext(): float {
		// get next max delay
		$this->counter++;
		$delay = $this->maxReached ? $this->maxDelay : min($this->maxDelay, self::BASE_DELAY ** $this->counter);
		if ($delay >= $this->maxDelay) {
			$this->maxReached = true;
		}
		// calculate jitter %
		$jitter = 0.25 - (0.15 * (($delay - self::BASE_DELAY) / ($this->maxDelay - self::BASE_DELAY)));
		// get min and max delay for attempt based on jitter
		$minDelay = $delay * (1 - $jitter);
		$maxDelay = $delay * (1 + $jitter);
		// get delay with jitter
		$delay = $this->randomizer->getInt(
			min: (int) round($minDelay * self::MILLIS),
			max: (int) round($maxDelay * self::MILLIS)
		);
		return $delay / self::MILLIS;
	}

	/**
	 * Returns counter
	 * @return int Counter
	 */
	public function getCounter(): int {
		return $this->counter;
	}

	/**
	 * Resets delay by setting it to mininum delay
	 */
	public function reset(): void {
		$this->counter = 0;
		$this->maxReached = false;
	}

}
