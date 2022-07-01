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

use App\Models\Database\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * Operator entity
 * @ORM\Entity(repositoryClass="App\Models\Database\Repositories\NetworkOperatorRepository")
 * @ORM\Table(name="network_operators")
 * @ORM\HasLifecycleCallbacks()
 */
class NetworkOperator implements JsonSerializable {

	use TId;

	/**
	 * @var string Operator name
	 * @ORM\Column(type="string", length=255)
	 */
	private string $name;

	/**
	 * @var string APN
	 * @ORM\Column(type="string", length=255)
	 */
	private string $apn;

	/**
	 * @var string|null Username
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private ?string $username;

	/**
	 * @var string|null Password
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private ?string $password;

	/**
	 * Constructor
	 * @param string $name Operator name
	 * @param string $apn APN
	 * @param string|null $username Username
	 * @param string|null $password Password
	 */
	public function __construct(string $name, string $apn, ?string $username = null, ?string $password = null) {
		$this->name = $name;
		$this->apn = $apn;
		$this->username = $username;
		$this->password = $password;
	}

	/**
	 * Returns operator name
	 * @return string Operator name
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Sets operator name
	 * @param string $name Operator name
	 */
	public function setName(string $name): void {
		$this->name = $name;
	}

	/**
	 * Returns APN
	 * @return string APN
	 */
	public function getApn(): string {
		return $this->apn;
	}

	/**
	 * Sets APN
	 * @param string $apn APN
	 */
	public function setApn(string $apn): void {
		$this->apn = $apn;
	}

	/**
	 * Returns username
	 * @return string|null Username
	 */
	public function getUsername(): ?string {
		return $this->username;
	}

	/**
	 * Sets username
	 * @param string|null $username Username
	 */
	public function setUsername(?string $username = null): void {
		$this->username = $username;
	}

	/**
	 * Returns password
	 * @return string|null Password
	 */
	public function getPassword(): ?string {
		return $this->password;
	}

	/**
	 * Sets password
	 * @param string|null $password Password
	 */
	public function setPassword(?string $password = null): void {
		$this->password = $password;
	}

	/**
	 * Returns JSON serialized operator data
	 * @return array{id: int|null, name: string, apn: string, username?: string, password?: string} JSON serialized operator data
	 */
	public function jsonSerialize(): array {
		$array = [
			'id' => $this->id,
			'name' => $this->name,
			'apn' => $this->apn,
		];
		if ($this->username !== null) {
			$array['username'] = $this->username;
		}
		if ($this->password !== null) {
			$array['password'] = $this->password;
		}
		return $array;
	}

}
