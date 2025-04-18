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
use App\Models\Database\Attributes\TUuid;
use App\Models\Database\Repositories\PasswordRecoveryRepository;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Password recovery
 */
#[ORM\Entity(repositoryClass: PasswordRecoveryRepository::class)]
#[ORM\Table(name: 'password_recovery')]
#[ORM\HasLifecycleCallbacks]
class PasswordRecovery {

	use TUuid;
	use TCreatedAt;

	/**
	 * Constructor
	 * @param User $user User
	 */
	public function __construct(
		#[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'])]
		#[ORM\JoinColumn(name: 'user', nullable: false, onDelete: 'CASCADE')]
		private User $user,
	) {
	}

	/**
	 * Returns the user
	 * @return User User
	 */
	public function getUser(): User {
		return $this->user;
	}

	/**
	 * Checks if the password recovery request is expired
	 * @return bool Is the password recovery request expired?
	 */
	public function isExpired(): bool {
		$expirationInterval = new DateInterval('P1D');
		$expiration = DateTimeImmutable::createFromMutable($this->createdAt)
			->add($expirationInterval);
		$now = new DateTimeImmutable();
		return $now >= $expiration;
	}

}
