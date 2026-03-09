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

namespace App\Models\Database\Entities;

use App\Enums\AccessScope;
use App\Models\Database\Attributes\TCreatedAt;
use App\Models\Database\Attributes\TId;
use App\Models\Database\Enums\ApiKeyState;
use App\Models\Database\Repositories\ApiKeyRepository;
use App\Models\Database\Types\AccessScopeArrayType;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use InvalidArgumentException;
use JsonSerializable;
use Throwable;

/**
 * API key entity
 */
#[ORM\Entity(repositoryClass: ApiKeyRepository::class)]
#[ORM\Table(name: 'api_keys_v2')]
#[ORM\HasLifecycleCallbacks]
class ApiKey implements JsonSerializable {

	use TId;
	use TCreatedAt;

	/**
	 * Identifier
	 */
	private const IDENT = 'webapp';

	/**
	 * @var string|null Secret
	 */
	private ?string $key = null;

	#[ORM\Column(type: Types::DATETIME_MUTABLE)]
	private DateTime $expiration;

	/**
	 * @var string Hashed salted secret
	 */
	#[ORM\Column(type: Types::STRING, length: 64, unique: true)]
	private string $hash;

	/**
	 * @var string Salt
	 */
	#[ORM\Column(type: Types::STRING, length: 32, unique: true)]
	private string $salt;

	/**
	 * @var User|null User who revoked API key
	 */
	#[ORM\ManyToOne(targetEntity: User::class)]
	#[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
	private ?User $revokedBy = null;

	/**
	 * @var DateTime|null Revoked at time
	 */
	#[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
	private ?DateTime $revokedAt = null;

	/**
	 * Constructor
	 * @param string $description Description
	 * @param DateTime $expiration Expiration
	 * @param User|null $createdBy API key creator
	 * @param ApiKeyState $state API key state
	 * @param array<AccessScope> $scopes API key scopes
	 */
	public function __construct(
		#[ORM\Column(type: Types::STRING, length: 255)]
		private string $description,
		DateTime $expiration,
		#[ORM\ManyToOne(targetEntity: User::class)]
		#[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
		private readonly ?User $createdBy,
		#[ORM\Column(type: Types::INTEGER, enumType: ApiKeyState::class, options: ['default' => ApiKeyState::Active])]
		private ApiKeyState $state = ApiKeyState::Active,
		#[ORM\Column(type: AccessScopeArrayType::ACCESS_SCOPE_ARRAY)]
		private array $scopes = [],
	) {
		$key = random_bytes(32);
		$salt = random_bytes(16);
		$this->key = base64_encode($key);
		$this->salt = base64_encode($salt);
		$this->hash = base64_encode(hash('sha256', $salt . $key, true));
		$this->setExpiration($expiration);
	}

	/**
	 * Get one-time display prefix and secret
	 * @return string Secret with prefix
	 */
	public function getKey(): string {
		return self::IDENT . ';' . $this->id . ';' . $this->key;
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
	 * @return DateTime API key expiration
	 */
	public function getExpiration(): DateTime {
		return $this->expiration;
	}

	/**
	 * Sets API key expiration
	 * @param DateTime $expiration API key expiration
	 */
	public function setExpiration(DateTime $expiration): void {
		$this->expiration = $expiration;
		if ($this->isExpired()) {
			throw new DomainException('Expiration date has already passed');
		}
	}

	/**
	 * Sets API key expiration from string
	 * @param string $expiration API key expiration
	 */
	public function setExpirationFromString(string $expiration): void {
		try {
			$this->expiration = new DateTime($expiration);
		} catch (Throwable) {
			throw new InvalidArgumentException('Invalid expiration date format');
		}
		if ($this->isExpired()) {
			throw new DomainException('Expiration date has already passed');
		}
	}

	/**
	 * Checks if API key is expired
	 * @return bool Is API key expired
	 */
	public function isExpired(): bool {
		$now = new DateTime();
		return $this->expiration < $now;
	}

	/**
	 * Returns user that created the API key
	 * @return User|null User that created key
	 */
	public function getCreatedBy(): ?User {
		return $this->createdBy;
	}

	/**
	 * Returns the API key state
	 * @return ApiKeyState API key state
	 */
	public function getState(): ApiKeyState {
		return $this->state;
	}

	/**
	 * Returns API key scopes
	 * @return array<AccessScope> API key scopes
	 */
	public function getScopes(): array {
		return $this->scopes;
	}

	/**
	 * Sets API key scopes
	 * @param array<AccessScope> $scopes API key scopes
	 */
	public function setScopes(array $scopes): void {
		$this->scopes = $scopes;
	}

	/**
	 * Sets scopes from array of strings corresponding to the API scope names.
	 * @param array<string> $scopes API key scopes
	 */
	public function setScopesFromStringArray(array $scopes): void {
		$this->scopes = array_map(
			function (string $val): AccessScope {
				$as = AccessScope::tryFrom($val);
				if ($as === null) {
					throw new DomainException('Invalid access scope ' . $val . '!');
				}
				return $as;
			},
			$scopes
		);
	}

	/**
	 * Returns user that revoked the API key
	 * @return User|null User that revoked key
	 */
	public function getRevokedBy(): ?User {
		return $this->revokedBy;
	}

	/**
	 * Returns revoked at time
	 * @return DateTime|null Revoked at time
	 */
	public function getRevokedAt(): ?DateTime {
		return $this->revokedAt;
	}

	/**
	 * Revoke the API key
	 */
	public function revoke(User $revokedBy): void {
		if ($this->state === ApiKeyState::Revoked) {
			return;
		}
		$this->state = $this->state->revoke();
		$this->revokedBy = $revokedBy;
		$this->revokedAt = new DateTime();
	}

	/**
	 * Verifies API key
	 * @param string $key API key
	 * @return bool Is API key
	 */
	public function verify(string $key): bool {
		$salt = base64_decode($this->salt, true);
		$hash = base64_decode($this->hash, true);
		$candidate = base64_decode($key, true);
		$candidateHash = hash('sha256', $salt . $candidate, true);
		return hash_equals($hash, $candidateHash);
	}

	/**
	 * Returns JSON serialized data
	 * @return array{
	 *     id: int|null,
	 *     description: string,
	 *     expiration: string|null,
	 *     createdBy: int|null,
	 *     createdAt: string,
	 *     state: string,
	 *     revokedBy: int|null,
	 *     revokedAt: string|null,
	 *     key?: string
	 * } JSON serialized data
	 */
	public function jsonSerialize(): array {
		$array = [
			'id' => $this->getId(),
			'description' => $this->getDescription(),
			'expiration' => $this->expiration->format('c'),
			'createdBy' => $this->createdBy?->getId(),
			'createdAt' => $this->getCreatedAt()->format('c'),
			'state' => $this->state->jsonSerialize(),
			'scopes' => array_map(static fn (AccessScope $item): string => $item->value, $this->scopes),
			'revokedBy' => $this->revokedBy?->getId(),
			'revokedAt' => $this->revokedAt?->format('c'),
		];
		if (isset($this->key)) {
			$array['key'] = $this->getKey();
		}
		return $array;
	}

}
