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

namespace App\Models\Database\Entities;

use App\Models\Database\Attributes\TId;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * API key entity
 * @ORM\Entity(repositoryClass="App\Models\Database\Repositories\ApiKeyRepository")
 * @ORM\Table(name="`api_keys`")
 * @ORM\HasLifecycleCallbacks()
 */
class ApiKey implements JsonSerializable {

	use TId;

	/**
	 * @var string API key
	 * @ORM\Column(type="string", length=255, nullable=false, unique=true)
	 */
	private $key;

	/**
	 * @var string API key description
	 * @ORM\Column(type="string", length=255, nullable=false, unique=false)
	 */
	private $description;

	/**
	 * @var DateTime|null API key expiration
	 * @ORM\Column(type="date", nullable=true)
	 */
	private $expiration;

	/**
	 * Constructor
	 * @param string $key API key
	 * @param string $description API key description
	 * @param DateTime|null $expiration API key expiration
	 */
	public function __construct(string $key, string $description, ?DateTime $expiration) {
		$this->key = $key;
		$this->description = $description;
		$this->expiration = $expiration;
	}

	/**
	 * Returns API key description
	 * @return string API key description
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * Returns API key expiration
	 * @return DateTime|null API key expiration
	 */
	public function getExpiration(): ?DateTime {
		return $this->expiration;
	}

	/**
	 * Returns API key
	 * @return string API key
	 */
	public function getKey(): string {
		return $this->key;
	}

	/**
	 * Checks if API key is expired
	 * @return bool Is API key expired
	 */
	public function isExpired(): bool {
		if ($this->expiration === null) {
			return false;
		}
		$now = new DateTime();
		return $this->expiration < $now;
	}

	/**
	 * Generates API key
	 * @return string API key
	 */
	public static function generate(): string {
		return bin2hex(random_bytes(32));
	}

	/**
	 * Returns JSON serialized data
	 * @return array<string, int|string|null> JSON serialized data
	 */
	public function jsonSerialize(): array {
		return [
			'id' => $this->getId(),
			'description' => $this->getDescription(),
			'expiration' => $this->getExpiration() === null ? null : $this->getExpiration()->format('c'),
		];
	}

}
