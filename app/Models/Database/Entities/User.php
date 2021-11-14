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

use App\Exceptions\InvalidUserLanguageException;
use App\Exceptions\InvalidUserRoleException;
use App\Models\Database\Attributes\TId;
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
	 * @var string User name
	 * @ORM\Column(type="string", length=255, unique=true)
	 */
	private $username;

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
	 * @var string User language
	 * @ORM\Column(type="string", length=7)
	 */
	private $language;

	/**
	 * Constructor
	 * @param string $username User name
	 * @param string $password User password
	 * @param string|null $role User role
	 * @param string|null $language User language
	 */
	public function __construct(string $username, string $password, ?string $role = null, ?string $language = null) {
		$this->username = $username;
		$this->setPassword($password);
		$this->setRole($role ?? self::ROLE_DEFAULT);
		$this->setLanguage($language ?? self::LANGUAGE_DEFAULT);
	}

	/**
	 * Returns the user name
	 * @return string User name
	 */
	public function getUserName(): string {
		return $this->username;
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
	 * @return array<string, int|string> JSON serialized User entity
	 */
	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'username' => $this->username,
			'role' => $this->role,
			'language' => $this->language,
		];
	}

}
