<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

use App\Exceptions\InvalidEmailAddressException;
use App\Exceptions\InvalidPasswordException;
use App\Exceptions\InvalidUserLanguageException;
use App\Exceptions\InvalidUserRoleException;
use App\Exceptions\InvalidUserStateException;
use App\Models\Database\Attributes\TId;
use App\Models\Database\Repositories\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;
use JsonSerializable;
use function in_array;
use function password_hash;
use function password_verify;
use const PASSWORD_DEFAULT;

/**
 * User entity
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\HasLifecycleCallbacks]
class User implements JsonSerializable {

	use TId;

	/**
	 * Language: English
	 */
	final public const LANGUAGE_ENGLISH = 'en';

	/**
	 * Default language
	 */
	final public const LANGUAGE_DEFAULT = self::LANGUAGE_ENGLISH;

	/**
	 * Supported languages
	 */
	final public const LANGUAGES = [self::LANGUAGE_ENGLISH];

	/**
	 * User role: Admin user
	 */
	final public const ROLE_ADMIN = 'admin';

	/**
	 * User role: Normal user
	 */
	final public const ROLE_NORMAL = 'normal';

	/**
	 * User role: Basic admin user
	 */
	final public const ROLE_BASICADMIN = 'basicadmin';

	/**
	 * User role: Basic user
	 */
	final public const ROLE_BASIC = 'basic';

	/**
	 * Default user role
	 */
	final public const ROLE_DEFAULT = self::ROLE_NORMAL;

	/**
	 * Supported user roles
	 */
	final public const ROLES = [
		self::ROLE_ADMIN,
		self::ROLE_NORMAL,
		self::ROLE_BASICADMIN,
		self::ROLE_BASIC,
	];

	/**
	 * Account state: unverified e-mail address
	 */
	final public const STATE_UNVERIFIED = 0;

	/**
	 * Account state: verified e-mail address
	 */
	final public const STATE_VERIFIED = 1;

	/**
	 * Account state: blocked account
	 */
	final public const STATE_BLOCKED = 2;

	/**
	 * Default account state
	 */
	final public const STATE_DEFAULT = self::STATE_UNVERIFIED;

	/**
	 * Supported account states
	 */
	final public const STATES = [
		self::STATE_UNVERIFIED => 'unverified',
		self::STATE_VERIFIED => 'verified',
		self::STATE_BLOCKED => 'blocked',
	];

	/**
	 * @var string User name
	 */
	#[ORM\Column(type: Types::STRING, length: 255, unique: true)]
	private string $username;

	/**
	 * @var string|null User's email
	 */
	#[ORM\Column(type: Types::STRING, length: 255, nullable: true, unique: true)]
	private ?string $email = null;

	/**
	 * @var string Password hash
	 */
	#[ORM\Column(type: Types::STRING, length: 255)]
	private string $password;

	/**
	 * @var string User role
	 */
	#[ORM\Column(type: Types::STRING, length: 15)]
	private string $role;

	/**
	 * @var int Account state
	 */
	#[ORM\Column(type: Types::INTEGER, length: 10, options: ['default' => self::STATE_DEFAULT])]
	private int $state = self::STATE_DEFAULT;

	/**
	 * @var string User language
	 */
	#[ORM\Column(type: Types::STRING, length: 7)]
	private string $language = self::LANGUAGE_DEFAULT;

	/**
	 * @var Collection<int, UserVerification> User verifications
	 */
	#[ORM\OneToMany(mappedBy: 'user', targetEntity: UserVerification::class, cascade: ['persist'], orphanRemoval: true)]
	private Collection $verifications;

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
		$this->setEmail($email);
		$this->setPassword($password);
		$this->setRole($role ?? self::ROLE_DEFAULT);
		$this->setLanguage($language ?? self::LANGUAGE_DEFAULT);
		$this->setState($state ?? self::STATE_DEFAULT);
		$this->verifications = new ArrayCollection();
	}

	/**
	 * Returns the username
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
	 * Returns all user scopes
	 * @return array<string> User scopes
	 */
	public function getScopes(): array {
		$scopes = [];
		if ($this->role === self::ROLE_BASICADMIN) {
			$scopes = array_merge($scopes, [
				'users:basic',
			]);
		}
		if ($this->role === self::ROLE_NORMAL || $this->role === self::ROLE_ADMIN) {
			$scopes = array_merge($scopes, [
				'clouds',
				'config:controller',
				'config:daemon',
				'config:iqrfRepository',
				'config:translator',
				'gateway:log',
				'gateway:power',
				'iqrf:macros',
			]);
		}
		if ($this->role === self::ROLE_ADMIN) {
			$scopes = array_merge($scopes, [
				'apiKeys',
				'iqrf:upload',
				'mailer',
				'maintenance:backup',
				'maintenance:mender',
				'maintenance:monit',
				'network',
				'users:admin',
				'sshKeys',
			]);
		}
		return $scopes;
	}

	/**
	 * Checks if the user has a scope
	 * @param string $scope Scope
	 * @return bool User has a scope
	 */
	public function hasScope(string $scope): bool {
		return in_array($scope, $this->getScopes(), true);
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
	 * @return Collection<int, UserVerification> E-mail address verifications
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
	 * @throws InvalidEmailAddressException
	 */
	public function setEmail(?string $email): void {
		if ($email === '') {
			$email = null;
		}
		if ($email !== null) {
			$this->validateEmail($email);
		}
		if ($this->email !== $email) {
			if ($this->getState() === self::STATE_VERIFIED) {
				$this->setState(self::STATE_UNVERIFIED);
			}
		}
		$this->email = $email;
	}
	/**
	 * Sets the user's password
	 * @param string $password User's password
	 */
	public function setPassword(string $password): void {
		if ($password === '') {
			throw new InvalidPasswordException();
		}
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
		if (!array_key_exists($state, self::STATES)) {
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
	 * Validates e-mail address
	 * @param string $email E-mail address to validate
	 */
	private function validateEmail(string $email): void {
		$validator = new EmailValidator();
		$validationRules = [
			new RFCValidation(),
		];
		if (function_exists('dns_get_record')) {
			$validationRules[] = new DNSCheckValidation();
		}
		if (!$validator->isValid($email, new MultipleValidationWithAnd($validationRules))) {
			$error = $validator->getError();
			if ($error === null) {
				throw new InvalidEmailAddressException();
			}
			throw new InvalidEmailAddressException($error->description(), $error->code());
		}
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
			'state' => self::STATES[$this->state],
		];
	}

}
