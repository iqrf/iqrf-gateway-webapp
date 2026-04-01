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
use App\Models\Database\Attributes\TId;
use App\Models\Database\Repositories\RoleRepository;
use App\Models\Database\Types\AccessScopeArrayType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use JsonSerializable;
use function in_array;

/**
 * User entity
 */
#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ORM\Table(name: 'roles')]
#[ORM\HasLifecycleCallbacks]
class Role implements JsonSerializable {

	use TId;

	/**
	 * @var string Role name
	 */
	#[ORM\Column(type: Types::STRING, length:255, unique: true, nullable: false)]
	private string $name;

	/**
	 * @var string Role description
	 */
	#[ORM\Column(type: Types::STRING, length: 255)]
	private string $description;

	/**
	 * @var Array<AccessScope> Access scopes
	 */
	#[ORM\Column(type: AccessScopeArrayType::ACCESS_SCOPE_ARRAY)]
	private array $scopes = [];

	/**
	 * @var bool System role flag
	 */
	#[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
	private bool $system = false;

	/**
	 * @var string Stable identifier of the system role
	 */
	#[ORM\Column(type: Types::STRING, length: 64, unique: true, nullable: true)]
	private ?string $systemKey = null;

	/**
	 * Constructor
	 * @param string $name Role name
	 * @param string $description Role description
	 * @param array<AccessScope>|array<string> $scopes Access scopes
	 * @param bool $system Is role system-managed
	 * @param string|null $systemKey Stable identifier of the system role
	 */
	public function __construct(
		string $name,
		string $description,
		array $scopes,
		bool $system = false,
		?string $systemKey = null,
	) {
		$this->name = $name;
		$this->description = $description;
		if ($scopes !== [] && gettype($scopes[0]) === 'string') {
			$this->setScopesFromStringArray($scopes);
		} else {
			$this->scopes = $scopes;
		}
		$this->system = $system;
		$this->systemKey = $systemKey;
	}

	/**
	 * Returns role name
	 * @return string Role name
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Sets role name
	 * @param string $name Role name
	 */
	public function setName(string $name): void {
		$this->name = $name;
	}

	/**
	 * Returns role description
	 * @return string Role description
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * Sets role description
	 * @param string $description Role description
	 */
	public function setDescription(string $description): void {
		$this->description = $description;
	}

	/**
	 * Returns role scopes
	 * @return array<AccessScope> Role scopes
	 */
	public function getScopes(): array {
		return $this->scopes;
	}

	/**
	 * Checks whether this is a system-managed role
	 * @return bool True if role is system-managed, false otherwise
	 */
	public function isSystem(): bool {
		return $this->system;
	}

	/**
	 * Returns stable system role identifier
	 * @return string|null Stable system role identifier
	 */
	public function getSystemKey(): ?string {
		return $this->systemKey;
	}

	/**
	 * Sets role scopes
	 * @param array<AccessScope> $scopes Role scopes
	 */
	public function setScopes(array $scopes): void {
		$this->scopes = $scopes;
	}

	/**
	 * Sets scopes from array of strings corresponding to the access scopes
	 * @param array<string> $scopes API key scopes
	 * @throws DomainException if given string does not corespond to any access scope
	 */
	public function setScopesFromStringArray(array $scopes): void {
		$this->scopes = AccessScope::parseScopesFromStringArray($scopes);
	}

	/**
	 * Checks if role has specific scope assigned to it
	 * @param AccessScope $scope Scope to check
	 * @return bool True if role has given scope assigned, False otherwise
	 */
	public function hasScope(AccessScope $scope): bool {
		return in_array($scope, $this->getScopes(), true);
	}

	/**
	 * Check if role has specific scope assigned to it. Scope is given by the string reprezentation.
	 * @param string $scope string reprezentation of scope to check
	 * @return bool True if role has given scope assigned, False otherwise
	 * @throws DomainException if given string does not corespond to any access scope
	 */
	public function hasScopeFromString(string $scope): bool {
		return $this->hasScope(AccessScope::parseScopeFromString($scope));
	}

	/**
	 * Returns JSON serialized data
	 * @return array{
	 *     id: int|null,
	 *     name: string,
	 *     description: string,
	 *     scopes: array<string>,
	 *     system: bool,
	 *     systemKey: string|null
	 * } JSON serialized data
	 */
	public function jsonSerialize(): array {
		return [
			'id' => $this->getId(),
			'name' => $this->getName(),
			'description' => $this->getDescription(),
			'scopes' => array_map(static fn (AccessScope $scope): string => $scope->value, $this->getScopes()),
			'system' => $this->isSystem(),
			'systemKey' => $this->getSystemKey(),
		];
	}

}
