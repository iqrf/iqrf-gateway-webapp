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

namespace App\Models\Database\Entities;

use App\Models\Database\Attributes\TCreatedAt;
use App\Models\Database\Attributes\TId;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * Ssh key entity
 * @ORM\Entity(repositoryClass="App\Models\Database\Repositories\SshKeyRepository")
 * @ORM\Table(name="`ssh_keys`")
 * @ORM\HasLifecycleCallbacks()
 */
class SshKey implements JsonSerializable {

	use TId;
	use TCreatedAt;

	/**
	 * @var string SSH key type
	 * @ORM\Column(type="string", length=255)
	 */
	private string $type;

	/**
	 * @var string SSH key
	 * @ORM\Column(type="string", length=2048, unique=true)
	 */
	private string $key;

	/**
	 * @var string SSH key hash
	 * @ORM\Column(type="string", length=64, unique=true)
	 */
	private string $hash;

	/**
	 * @var string|null SSH key description
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private ?string $description;

	/**
	 * Constructor
	 * @param string $type SSH key type
	 * @param string $key SSH key
	 * @param string $hash SSH key hash
	 * @param string|null $description SSH key description
	 */
	public function __construct(string $type, string $key, string $hash, ?string $description = null) {
		$this->type = $type;
		$this->key = $key;
		$this->hash = $hash;
		$this->description = $description;
	}

	/**
	 * Returns SSH key type
	 * @return string SSH key type
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * Sets SSH key type
	 * @param string $type SSH key type
	 */
	public function setType(string $type): void {
		$this->type = $type;
	}

	/**
	 * Returns SSH key
	 * @return string SSH key
	 */
	public function getKey(): string {
		return $this->key;
	}

	/**
	 * Sets SSH key
	 * @param string $key SSH key
	 */
	public function setKey(string $key): void {
		$this->key = $key;
	}

	/**
	 * Returns SSH key hash
	 * @return string SSH key hash
	 */
	public function getHash(): string {
		return $this->hash;
	}

	/**
	 * Sets SSH key hash
	 * @param string $hash SSH key hash
	 */
	public function setHash(string $hash): void {
		$this->hash = $hash;
	}

	/**
	 * Returns SSH key description
	 * @return string|null SSH key description
	 */
	public function getDescription(): ?string {
		return $this->description;
	}

	/**
	 * Sets SSH key description
	 * @param string|null $description SSH key description
	 */
	public function setDescription(?string $description = null): void {
		$this->description = $description;
	}

	/**
	 * Returns JSON serialized ssh key entity
	 * @return array{id: int|null, type: string, key: string, hash: string, description: string|null, createdAt: string} JSON serialized entity
	 */
	public function jsonSerialize(): array {
		return [
			'id' => $this->getId(),
			'type' => $this->getType(),
			'key' => $this->toString(),
			'hash' => $this->getHash(),
			'description' => $this->getDescription(),
			'createdAt' => $this->getCreatedAt()->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z'),
		];
	}

	/**
	 * Returns string representation of the SSH key entity
	 * @return string SSH key string
	 */
	public function toString(): string {
		$chunks = [
			$this->getType(),
			$this->getKey(),
			$this->getDescription() ?? '',
		];
		return trim(implode(' ', $chunks));
	}

}
