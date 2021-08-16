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

namespace App\Models\Database\Entities;

use App\Exceptions\InvalidUserLanguageException;
use App\Exceptions\InvalidUserRoleException;
use App\Exceptions\InvalidUserStateException;
use App\Models\Database\Attributes\TId;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use function in_array;
use function password_hash;
use function password_verify;
use const PASSWORD_DEFAULT;

/**
 * User entity
 * @ORM\Entity(repositoryClass="App\Models\Database\Repositories\UserRepository")
 * @ORM\Table(name="`users`")
 * @ORM\HasLifecycleCallbacks()
 */
class User implements JsonSerializable {

	use TId;

	/**
	 * Language: English
	 */
	public const LANGUAGE_ENGLISH = 'en';

	/**
	 * Default language
	 */
	public const LANGUAGE_DEFAULT = self::LANGUAGE_ENGLISH;

	/**
	 * Supported languages
	 */
	public const LANGUAGES = [self::LANGUAGE_ENGLISH];

	/**
	 * User role: Normal user
	 */
	public const ROLE_NORMAL = 'normal';

	/**
	 * User role: Power user
	 */
	public const ROLE_POWER = 'power';

	/**
	 * Default user role
	 */
	public const ROLE_DEFAULT = self::ROLE_NORMAL;

	/**
	 * Supported user roles
	 */
	public const ROLES = [self::ROLE_NORMAL, self::ROLE_POWER];

	/**
	 * Account state: unverified e-mail address
	 */
	public const STATE_UNVERIFIED = 0;

	/**
	 * Account state: verified e-mail address
	 */
	public const STATE_VERIFIED = 1;

	/**
	 * Account state: blocked account
	 */
	public const STATE_BLOCKED = 2;

	/**
	 * Default account state
	 */
	public const STATE_DEFAULT = self::STATE_UNVERIFIED;

	/**
	 * Supported account states
	 */
	public const STATES = [
		self::STATE_UNVERIFIED,
		self::STATE_VERIFIED,
		self::STATE_BLOCKED,
	];

	/**
	 * @var string User name
	 * @ORM\Column(type="string", length=255, unique=true)
	 */
	private $username;

	/**
	 * @var string|null User's email
	 * @ORM\Column(type="string", length=255, nullable=true, unique=true)
	 */
	private $email;

	/**
	 * @var string Password hash
	 * @ORM\Column(type="string", length=255)
	 */
	private $password;

	/**
	 * @var string User role
	 * @ORM\Column(type="string", length=15)
	 */
	private $role;

	/**
	 * @var int Account state
	 * @ORM\Column(type="integer", length=10, nullable=FALSE, unique=FALSE, options={"default" : 0})
	 */
	private $state;

	/**
	 * @var string User language
	 * @ORM\Column(type="string", length=7)
	 */
	private $language;

	/**
	 * @var Collection<UserVerification> User verifications
	 * @ORM\OneToMany(targetEntity="UserVerification", mappedBy="user", orphanRemoval=true, cascade={"persist"})
	 */
	private $verifications;

	/**
	 * Constructor
	 * @param string $username User name
	 * @param string|null $email User's email
	 * @param string $password User password
	 * @param string|null $role User role
	 * @param string|null $language User language
	 * @param int|null $state Account state
	 */
	public function __construct(string $username, ?string $email, string $password, ?string $role = null, ?string $language = null, ?int $state = null) {
		$this->username = $username;
		$this->email = $email;
		$this->setPassword($password);
		$this->setRole($role ?? self::ROLE_DEFAULT);
		$this->setLanguage($language ?? self::LANGUAGE_DEFAULT);
		$this->setState($state ?? self::STATE_DEFAULT);
	}

	/**
	 * Returns the user name
	 * @return string User name
	 */
	public function getUserName(): string {
		return $this->username;
	}

	/**
	 * Returns the user's email
	 * @return string|null User's email
	 */
	public function getEmail(): ?string {
		return $this->email;
	}

	/**
	 * Returns the password hash
	 * @return string Password hash
	 */
	public function getPassword(): string {
		return $this->password;
	}

	/**
	 * Returns the user's role
	 * @return string User's role
	 */
	public function getRole(): string {
		return $this->role;
	}

	/**
	 * Returns the user's language
	 * @return string User's language
	 */
	public function getLanguage(): string {
		return $this->language;
	}

	/**
	 * Sets the user name
	 * @param string $userName User name
	 */
	public function setUserName(string $userName): void {
		$this->username = $userName;
	}

	/**
	 * Returns the user's account state
	 * @return int User's account state
	 */
	public function getState(): int {
		return $this->state;
	}

	/**
	 * Returns all e-mail address verification
	 * @return Collection<UserVerification> E-mail address verifications
	 */
	public function getVerifications(): Collection {
		return $this->verifications;
	}

	/**
	 * Clears all e-mail address verifications
	 */
	public function clearVerifications(): void {
		$this->verifications->clear();
	}

	/**
	 * Sets the user's email
	 * @param string|null $email User's email
	 */
	public function setEmail(?string $email): void {
		$this->email = $email;
	}

	/**
	 * Sets the user's password
	 * @param string $password User's password
	 */
	public function setPassword(string $password): void {
		$this->password = password_hash($password, PASSWORD_DEFAULT);
	}

	/**
	 * Sets the user role
	 * @param string $role User role
	 */
	public function setRole(string $role): void {
		if (!in_array($role, self::ROLES, true)) {
			throw new InvalidUserRoleException();
		}
		$this->role = $role;
	}

	/**
	 * Sets the user's state
	 * @param int $state User's state
	 * @throws InvalidUserStateException
	 */
	public function setState(int $state): void {
		if (!in_array($state, self::STATES, true)) {
			throw new InvalidUserStateException();
		}
		$this->state = $state;
	}

	/**
	 * Sets the user language
	 * @param string $language User language
	 */
	public function setLanguage(string $language): void {
		if (!in_array($language, self::LANGUAGES, true)) {
			throw new InvalidUserLanguageException();
		}
		$this->language = $language;
	}

	/**
	 * Verifies the password
	 * @param string $password Password to verify
	 * @return bool Is the password correct?
	 */
	public function verifyPassword(string $password): bool {
		return password_verify($password, $this->password);
	}

	/**
	 * Returns the JSON serialized User entity
	 * @return array<string, int|string|null> JSON serialized User entity
	 */
	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'username' => $this->username,
			'email' => $this->email,
			'role' => $this->role,
			'language' => $this->language,
			'state' => $this->state,
		];
	}

}
