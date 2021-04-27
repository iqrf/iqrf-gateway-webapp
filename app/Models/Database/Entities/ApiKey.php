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

use App\Exceptions\ApiKeyExpirationPassedException;
use App\Exceptions\ApiKeyInvalidExpirationException;
use App\Models\Database\Attributes\TId;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Nette\Utils\Strings;
use Throwable;
use function base64_encode;
use function password_hash;
use function random_bytes;
use const PASSWORD_BCRYPT;

/**
 * API key entity
 * @ORM\Entity(repositoryClass="App\Models\Database\Repositories\ApiKeyRepository")
 * @ORM\Table(name="`api_keys`")
 * @ORM\HasLifecycleCallbacks()
 */
class ApiKey implements JsonSerializable {

	use TId;

	/**
	 * @var string|null API key
	 */
	private $key;

	/**
	 * @var string API key hash
	 * @ORM\Column(type="string", length=255, unique=true)
	 */
	private $hash;

	/**
	 * @var string API key hash salt
	 * @ORM\Column(type="string", length=22, unique=true)
	 */
	private $salt;

	/**
	 * @var string API key description
	 * @ORM\Column(type="string", length=255)
	 */
	private $description;

	/**
	 * @var DateTime|null API key expiration
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $expiration;

	/**
	 * Constructor
	 * @param string $description API key description
	 * @param DateTime|null $expiration API key expiration
	 */
	public function __construct(string $description, ?DateTime $expiration) {
		$this->key = base64_encode(random_bytes(32));
		$this->hash = password_hash($this->key, PASSWORD_BCRYPT);
		$saltHash = explode('$', $this->hash);
		$this->salt = Strings::substring(end($saltHash), 0, 22);
		$this->description = $description;
		$this->expiration = $expiration;
	}

	/**
	 * Returns API key
	 * @return string|null API key
	 */
	public function getKey(): ?string {
		return $this->salt . '.' . $this->key;
	}

	/**
	 * Returns API key hash
	 * @return string API key hash
	 */
	public function getHash(): string {
		return $this->hash;
	}

	/**
	 * Returns API key description
	 * @return string API key description
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * Sets API key description
	 * @param string $description API key description
	 */
	public function setDescription(string $description): void {
		$this->description = $description;
	}

	/**
	 * Returns API key expiration
	 * @return DateTime|null API key expiration
	 */
	public function getExpiration(): ?DateTime {
		return $this->expiration;
	}

	/**
	 * Sets API key expiration
	 * @param DateTime|null $expiration API key expiration
	 */
	public function setExpiration(?DateTime $expiration): void {
		$this->expiration = $expiration;
		if ($this->isExpired()) {
			throw new ApiKeyExpirationPassedException('Expiration date has already passed');
		}
	}

	/**
	 * Sets API key expiration from string
	 * @param string|null $expiration API key expiration
	 */
	public function setExpirationFromString(?string $expiration): void {
		if ($expiration === null) {
			$this->expiration = null;
			return;
		}
		try {
			$this->expiration = new DateTime($expiration);
		} catch (Throwable $e) {
			throw new ApiKeyInvalidExpirationException('Invalid expiration date');
		}
		if ($this->isExpired()) {
			throw new ApiKeyExpirationPassedException('Expiration date has already passed');
		}
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
	 * Verifies API key
	 * @param string $key API key
	 * @return bool Is API key
	 */
	public function verify(string $key): bool {
		return password_verify(Strings::substring($key, 23), $this->hash);
	}

	/**
	 * Returns JSON serialized data
	 * @return array<string, int|string|null> JSON serialized data
	 */
	public function jsonSerialize(): array {
		$array = [
			'id' => $this->getId(),
			'description' => $this->getDescription(),
			'expiration' => $this->getExpiration() === null ? null : $this->getExpiration()->format('c'),
		];
		if ($this->key !== null) {
			$array['key'] = $this->getKey();
		}
		return $array;
	}

}
