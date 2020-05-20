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
use Doctrine\ORM\Mapping as ORM;
use Nette\Security\Identity;
use Nette\Security\IIdentity;
use const PASSWORD_DEFAULT;

/**
 * User entity
 * @ORM\Entity(repositoryClass="App\Models\Database\Repositories\UserRepository")
 * @ORM\Table(name="`users`")
 * @ORM\HasLifecycleCallbacks()
 */
class User {

	use TId;

	/**
	 * @var string User name
	 * @ORM\Column(type="string", length=255, nullable=false, unique=true)
	 */
	private $username;

	/**
	 * @var string Password hash
	 * @ORM\Column(type="string", length=255, nullable=false, unique=false)
	 */
	private $password;

	/**
	 * @var string User role
	 * @ORM\Column(type="string", length=15, nullable=false, unique=false)
	 */
	private $role;

	/**
	 * @var string User language
	 * @ORM\Column(type="string", length=7, nullable=false, unique=false)
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
		$this->password = $password;
		$this->role = $role;
		$this->language = $language;
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
		$this->role = $role;
	}

	/**
	 * Sets the user language
	 * @param string $language User language
	 */
	public function setLanguage(string $language): void {
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
	 * Converts this entity to an array
	 * @return array<string, int|string> User entity as an array
	 */
	public function toArray(): array {
		return [
			'id' => $this->id,
			'username' => $this->username,
			'password' => $this->password,
			'role' => $this->role,
			'language' => $this->language,
		];
	}

	/**
	 * Returns user's identity
	 * @return IIdentity User's identity
	 */
	public function toIdentity(): IIdentity {
		return new Identity($this->id, [$this->role], [
			'username' => $this->username,
			'language' => $this->language,
		]);
	}

}
