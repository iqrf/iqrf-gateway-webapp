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
declare(strict_types=1);

namespace Database\Fixtures;

use App\ConfigModule\Enums\DeviceTypes;
use App\Models\Database\Entities\ControllerPinConfiguration;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ObjectManager;

/**
 * Controller pin fixture
 */
class ControllerPinConfigurationFixture implements FixtureInterface, OrderedFixtureInterface {
	/**
	 * Loads controller pins data into database
	 * @param ObjectManager $manager Object manager
	 */
	public function load(ObjectManager $manager): void {
		$repository = $manager->getRepository(ControllerPinConfiguration::class);
		$queryBuilder = $repository->createQueryBuilder('c');

		$records = [
			new ControllerPinConfiguration('IQD-GW-01', DeviceTypes::BOARD, 0, 1, 2),
			new ControllerPinConfiguration('IQD-GW-02', DeviceTypes::BOARD, 0, 1, 2, 11, 12),
			new ControllerPinConfiguration('Unipi Iris Zulu', DeviceTypes::BOARD, 507, 506, 504),
			new ControllerPinConfiguration('IQD-GW04', DeviceTypes::BOARD, 27, 17, 25, -1, 16),
			new ControllerPinConfiguration('KONA-RASP-04', DeviceTypes::ADAPTER, 27, 17, 25, -1, 16),
		];

		foreach ($records as $record) {
			if (!$queryBuilder->getParameters()->isEmpty()) {
				$queryBuilder->setParameters([]);
			}
			$queryBuilder->select('count(c.id)')
				->where('c.name = :name')->setParameter('name', $record->getName(), Types::STRING)
				->andWhere('c.greenLed = :greenLed')->setParameter('greenLed', $record->getGreenLedPin(), Types::INTEGER)
				->andWhere('c.redLed = :redLed')->setParameter('redLed', $record->getRedLedPin(), Types::INTEGER)
				->andWhere('c.button = :button')->setParameter('button', $record->getButtonPin(), Types::INTEGER);
			if ($record->getSckPin() !== null) {
				$queryBuilder->andWhere('c.sck = :sck')->setParameter('sck', $record->getSckPin(), Types::INTEGER);
			}
			if ($record->getSdaPin() !== null) {
				$queryBuilder->andWhere('c.sda = :sda')->setParameter('sda', $record->getSdaPin(), Types::INTEGER);
			}
			$count = intval($queryBuilder->getQuery()->getSingleScalarResult());
			if ($count === 0) {
				$manager->persist($record);
			}
		}
		$manager->flush();
	}

	/**
	 * Returns order of this fixture
	 */
	public function getOrder(): int {
		return 2;
	}
}
