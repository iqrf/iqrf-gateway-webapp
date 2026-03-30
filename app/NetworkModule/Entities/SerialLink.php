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

namespace App\NetworkModule\Entities;

use App\NetworkModule\Utils\NmCliConnection;
use stdClass;

/**
 * Serial link entity
 */
final readonly class SerialLink implements INetworkManagerEntity {

	/**
	 * nmcli configuration prefix
	 */
	public const NMCLI_PREFIX = 'serial';

	/**
	 * Constructor
	 * @param positive-int $baudRate Baud rate
	 * @param positive-int $bits Byte-width
	 * @param 'E'|'o'|'n'|'' $parity Connection parity 'E' for even, 'o' for odd, 'n' for none
	 * @param positive-int $sendDelay Delay between bytes in microseconds
	 * @param int<1,2> $stopBits Stop bits 1 or 2
	 */
	public function __construct(
		private int $baudRate,
		private int $bits,
		private string $parity,
		private int $sendDelay,
		private int $stopBits,
	) {
	}

	/**
	 * Deserializes Serial link entity from JSON
	 * @param stdClass $json JSON serialized entity
	 * @return SerialLink Serial link entity
	 */
	public static function jsonDeserialize(stdClass $json): INetworkManagerEntity {
		$baudRate = (int) ($json->baudRate ?? 57600);
		$bits = (int) ($json->bits ?? 8);
		$parity = $json->parity ?? '';
		$sendDelay = (int) ($json->sendDelay ?? 0);
		$stopBits = (int) ($json->stopBits ?? 1);
		return new self($baudRate, $bits, $parity, $sendDelay, $stopBits);
	}

	/**
	 * Deserializes Serial link from nmcli connection string
	 * @param array<string, array<string, array<string>|string>> $nmCli nmcli connection configuration
	 * @return SerialLink Serial link entity
	 */
	public static function nmCliDeserialize(array $nmCli): INetworkManagerEntity {
		$array = $nmCli[self::NMCLI_PREFIX];
		$baudRate = (int) ($array['baud'] ?? 57600);
		$bits = (int) ($array['bits'] ?? 8);
		$parity = $array['parity'] ?? '';
		if (!in_array($parity, ['E', 'o', 'n', ''], true)) {
			$parity = '';
		}
		$sendDelay = (int) ($array['send-delay'] ?? 0);
		$stopBits = (int) ($array['stopbits'] ?? 1);
		return new self($baudRate, $bits, $parity, $sendDelay, $stopBits);
	}

	/**
	 * Serializes Serial link entity into JSON
	 * @return array{baudRate: int, bits: int<1, max>, parity: 'E'|'n'|'o'|'', sendDelay: int, stopBits: int<1, 2>} JSON serialized entity
	 */
	public function jsonSerialize(): array {
		return [
			'baudRate' => $this->baudRate,
			'bits' => $this->bits,
			'parity' => $this->parity,
			'sendDelay' => $this->sendDelay,
			'stopBits' => $this->stopBits,
		];
	}

	/**
	 * Serializes Serial link entity into nmcli configuration string
	 * @return string nmcli configuration
	 */
	public function nmCliSerialize(): string {
		$array = [
			'baud' => $this->baudRate,
			'bits' => $this->bits,
			'parity' => $this->parity,
			'send-delay' => $this->sendDelay,
			'stopbits' => $this->stopBits,
		];
		return NmCliConnection::encode($array, self::NMCLI_PREFIX);
	}

}
