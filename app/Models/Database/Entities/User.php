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

use App\Exceptions\IncorrectPasswordException;
use App\Exceptions\InvalidEmailAddressException;
use App\Exceptions\InvalidPasswordException;
use App\Exceptions\InvalidUserStateException;
use App\Models\Database\Attributes\TId;
use App\Models\Database\Enums\UserLanguage;
use App\Models\Database\Enums\UserRole;
use App\Models\Database\Repositories\UserRepository;
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
	#[ORM\Column(type: Types::STRING, length: 255, unique: true, nullable: true)]
	private ?string $email = null;

	/**
	 * @var string|null Password hash
	 */
	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	private ?string $password = null;

	/**
	 * @var UserRole User role
	 */
	#[ORM\Column(type: Types::STRING, length: 15, enumType: UserRole::class, options: ['default' => UserRole::Default])]
	private UserRole $role;

	/**
	 * @var int Account state
	 */
	#[ORM\Column(type: Types::INTEGER, length: 10, options: ['default' => self::STATE_DEFAULT])]
	private int $state = self::STATE_DEFAULT;

	/**
	 * @var UserLanguage User language
	 */
	#[ORM\Column(type: 'string', length: 7, enumType: UserLanguage::class, options: ['default' => UserLanguage::Default])]
	private UserLanguage $language = UserLanguage::Default;

	/**
	 * @var UserVerification|null User verification
	 */
	#[ORM\OneToOne(mappedBy: 'user', targetEntity: UserVerification::class, cascade: ['persist', 'refresh', 'remove'], orphanRemoval: true)]
	public ?UserVerification $verification = null;

	/**
	 * @var bool Email changed
	 */
	private bool $emailChanged;

	/**
	 * @var bool Password changed
	 */
	private bool $passwordChanged;

	/**
	 * Constructor
	 * @param string $username User name
	 * @param string|null $email User's email
	 * @param string $password User password
	 * @param UserRole|null $role User role
	 * @param UserLanguage|null $language User language
	 * @param int|null $state Account state
	 */
	public function __construct(string $username, ?string $email, string $password, ?UserRole $role = null, ?UserLanguage $language = null, ?int $state = null) {
		$this->username = $username;
		$this->setEmail($email);
		$this->setPassword($password);
		$this->setRole($role ?? UserRole::Default);
		$this->setLanguage($language ?? UserLanguage::Default);
		$this->setState($state ?? self::STATE_DEFAULT);
		$this->emailChanged = false;
		$this->passwordChanged = false;
	}

	/**
	 * Checks if the user has changed e-mail
	 * @return bool User has changed e-mail
	 */
	public function hasChangedEmail(): bool {
		return $this->emailChanged;
	}

	/**
	 * Checks if the user has changed password
	 * @return bool User has changed password
	 */
	public function hasChangedPassword(): bool {
		return $this->passwordChanged;
	}

	/**
	 * Changes the user's password
	 * @param string $oldPassword Current password
	 * @param string $newPassword New password to set
	 * @throws IncorrectPasswordException Incorrect current password
	 * @throws InvalidPasswordException Invalid new password
	 */
	public function changePassword(string $oldPassword, string $newPassword): void {
		if (!$this->verifyPassword($oldPassword)) {
			throw new IncorrectPasswordException('Incorrect current password.');
		}
		$this->setPassword($newPassword);
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
	 * @return UserRole User's role
	 */
	public function getRole(): UserRole {
		return $this->role;
	}

	/**
	 * Returns the user's language
	 * @return UserLanguage User's language
	 */
	public function getLanguage(): UserLanguage {
		return $this->language;
	}

	/**
	 * Returns all user scopes
	 * @return array<string> User scopes
	 */
	public function getScopes(): array {
		$scopes = [];
		if ($this->role === UserRole::BasicAdmin) {
			$scopes = array_merge($scopes, [
				'users:basic',
			]);
		}
		if ($this->role === UserRole::Normal || $this->role === UserRole::Admin) {
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
		if ($this->role === UserRole::Admin) {
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
		if ($this->email !== $email && $this->getState() === self::STATE_VERIFIED) {
			$this->setState(self::STATE_UNVERIFIED);
			$this->emailChanged = true;
		}
		$this->email = $email;
	}

	/**
	 * Sets the user's password
	 * @param string $password User's password
	 * @throws InvalidPasswordException Invalid password
	 */
	public function setPassword(string $password): void {
		if ($password === '') {
			throw new InvalidPasswordException('Empty new password.');
		}
		$this->passwordChanged = !$this->verifyPassword($password);
		$this->password = password_hash($password, PASSWORD_DEFAULT);
	}

	/**
	 * Sets the user role
	 * @param UserRole $role User role
	 */
	public function setRole(UserRole $role): void {
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
	 * @param UserLanguage $language User language
	 */
	public function setLanguage(UserLanguage $language): void {
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
		if (EMAIL_VALIDATE_DNS && function_exists('dns_get_record')) {
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
		if ($this->password === null) {
			return false;
		}
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
			'role' => $this->role->value,
			'language' => $this->language->value,
			'state' => self::STATES[$this->state],
		];
	}

}
