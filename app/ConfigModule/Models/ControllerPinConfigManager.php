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

namespace App\ConfigModule\Models;

use App\ConfigModule\Enums\DeviceTypes;
use App\ConfigModule\Exceptions\ControllerPinConfigNotFoundException;
use App\Models\Database\Entities\ControllerPinConfiguration;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\ControllerPinConfigurationRepository;
use stdClass;

/**
 * IQRF Gateway Controller pin configuration manager
 */
class ControllerPinConfigManager {

	/**
	 * @var ControllerPinConfigurationRepository Controller pins repository
	 */
	private readonly ControllerPinConfigurationRepository $repository;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 */
	public function __construct(
		private readonly EntityManager $entityManager,
	) {
		$this->repository = $entityManager->getControllerPinConfigurationRepository();
	}

	/**
	 * Returns list of Controller pin configuration profiles
	 * @return array<int, ControllerPinConfiguration> List of Controller pin configuration profiles
	 */
	public function listPinConfigs(): array {
		return $this->repository->findAll();
	}

	/**
	 * Returns a Controller pin configuration profile
	 * @param int $id Controller pin configuration profile ID
	 * @return ControllerPinConfiguration DB entity
	 * @throws ControllerPinConfigNotFoundException
	 */
	public function getPinConfig(int $id): ControllerPinConfiguration {
		return $this->findPinConfig($id);
	}

	/**
	 * Adds a new Controller pin configuration profile
	 * @param stdClass $json Configuration json object
	 * @return ControllerPinConfiguration New DB entity
	 */
	public function addPinConfig(stdClass $json): ControllerPinConfiguration {
		$profile = new ControllerPinConfiguration(
			$json->name,
			DeviceTypes::from($json->deviceType),
			$json->greenLed,
			$json->redLed,
			$json->button
		);
		if (property_exists($json, 'sck')) {
			$profile->setSckPin($json->sck);
		}
		if (property_exists($json, 'sda')) {
			$profile->setSdaPin($json->sda);
		}
		$this->entityManager->persist($profile);
		$this->entityManager->flush();
		return $profile;
	}

	/**
	 * Edits a Controller pin configuration profile
	 * @param int $id Controller pin configuration profile ID
	 * @param stdClass $json Configuration json object
	 * @throws ControllerPinConfigNotFoundException
	 */
	public function editPinConfig(int $id, stdClass $json): void {
		$profile = $this->findPinConfig($id);
		$profile->setName($json->name);
		$profile->setDeviceType(DeviceTypes::from($json->deviceType));
		$profile->setGreenLedPin($json->greenLed);
		$profile->setRedLedPin($json->redLed);
		$profile->setButtonPin($json->button);
		$profile->setSckPin($json->sck ?? null);
		$profile->setSdaPin($json->sda ?? null);
		$this->entityManager->persist($profile);
		$this->entityManager->flush();
	}

	/**
	 * Removes Controller pin configuration profile
	 * @param int $id Controller pin configuration profile ID
	 * @throws ControllerPinConfigNotFoundException
	 */
	public function removePinConfig(int $id): void {
		$profile = $this->findPinConfig($id);
		$this->entityManager->remove($profile);
		$this->entityManager->flush();
	}

	/**
	 * Finds Controller pin configuration profile in database
	 * @param int $id Controller pin configuration profile ID
	 * @return ControllerPinConfiguration DB entity
	 * @throws ControllerPinConfigNotFoundException
	 */
	private function findPinConfig(int $id): ControllerPinConfiguration {
		$profile = $this->repository->find($id);
		if ($profile === null) {
			throw new ControllerPinConfigNotFoundException('Controller pin configuration profile not found');
		}
		return $profile;
	}

}
