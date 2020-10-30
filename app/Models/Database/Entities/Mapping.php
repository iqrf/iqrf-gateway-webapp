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
use JsonSerializable;

/**
 * Mapping entity
 * @ORM\Entity(repositoryClass="App\Models\Database\Repositories\MappingRepository")
 * @ORM\Table(name="mappings")
 * @ORM\HasLifecycleCallbacks()
 */
class Mapping implements JsonSerializable {

	use TId;

	/**
	 * @var string Mapping name
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	private $name;

	/**
	 * @var string Device name
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	private $iqrfInterface;

	/**
	 * @var int Bus enable pin
	 * @ORM\Column(type="integer", nullable=false)
	 */
	private $busEnableGpioPin;

	/**
	 * @var int Programming mode switch pin
	 * @ORM\Column(type="integer", nullable=false)
	 */
	private $pgmSwitchGpioPin;

	/**
	 * @var int Power enable pin
	 * @ORM\Column(type="integer", nullable=false)
	 */
	private $powerEnableGpioPin;

	/**
	 * Constructor
	 * @param string $name Mapping name
	 * @param string $iqrfInterface Mapping device name
	 * @param int $busEnableGpioPin Mapping bus enable pin
	 * @param int $pgmSwitchGpioPin Mapping programming mode switch pin
	 * @param int $powerEnableGpioPin Mapping power enable pin
	 */
	public function __construct(string $name, string $iqrfInterface, int $busEnableGpioPin, int $pgmSwitchGpioPin, int $powerEnableGpioPin) {
		$this->name = $name;
		$this->iqrfInterface = $iqrfInterface;
		$this->busEnableGpioPin = $busEnableGpioPin;
		$this->pgmSwitchGpioPin = $pgmSwitchGpioPin;
		$this->powerEnableGpioPin = $powerEnableGpioPin;
	}

	/**
	 * Returns mapping name
	 * @return string Mapping name
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Returns mapping device name
	 * @return string Mapping device name
	 */
	public function getInterface(): string {
		return $this->iqrfInterface;
	}

	/**
	 * Returns mapping bus enable pin
	 * @return int Mapping bus enable pin
	 */
	public function getBusPin(): int {
		return $this->busEnableGpioPin;
	}

	/**
	 * Returns mapping programming mode switch pin
	 * @return int Mapping programming mode switch pin
	 */
	public function getPgmPin(): int {
		return $this->pgmSwitchGpioPin;
	}

	/**
	 * Returns mapping power enable pin
	 * @return int Mapping power enable pin
	 */
	public function getPowerPin(): int {
		return $this->powerEnableGpioPin;
	}

	/**
	 * Returns JSON serialized mapping data
	 * @return array<string, array<string, int|string>> JSON serialized mapping data
	 */
	public function jsonSerialize(): array {
		$array = [
			$this->getName() => [
				'IqrfInterface' => $this->getInterface(),
				'busEnableGpioPin' => $this->getBusPin(),
				'pgmSwitchGpioPin' => $this->getPgmPin(),
				'powerEnableGpioPin' => $this->getPowerPin(),
			],
		];
		return $array;
	}

}
