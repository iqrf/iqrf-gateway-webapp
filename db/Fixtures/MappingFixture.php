<?php

/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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

use App\Models\Database\Entities\Mapping;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ObjectManager;

/**
 * Mapping fixture
 */
class MappingFixture implements FixtureInterface, OrderedFixtureInterface {
	/**
	 * Loads mapping data into database
	 * @param ObjectManager $manager Object manager
	 */
	public function load(ObjectManager $manager): void {
		$repository = $manager->getRepository(Mapping::class);
		$queryBuilder = $repository->createQueryBuilder('m');

		$records = [
			new Mapping(Mapping::TYPE_SPI, 'Unipi Iris Zulu', '/dev/spidev1.0', -1, 510, 511, null, 509, 508, 505),
			new Mapping(Mapping::TYPE_UART, 'Unipi Iris Zulu', '/dev/ttymxc3', -1, 510, 511, Mapping::BAUD_RATE_DEFAULT, 509, 508, 505),
		];

		foreach ($records as $record) {
			if (!$queryBuilder->getParameters()->isEmpty()) {
				$queryBuilder->setParameters([]);
			}
			$queryBuilder->select('count(m.id)')
				->where('m.type = :type')->setParameter('type', $record->getType(), Types::STRING)
				->andWhere('m.name = :name')->setParameter('name', $record->getName(), Types::STRING)
				->andWhere('m.iqrfInterface = :interface')->setParameter('interface', $record->getInterface(), Types::STRING)
				->andWhere('m.busEnableGpioPin = :busPin')->setParameter('busPin', $record->getBusPin(), Types::INTEGER)
				->andWhere('m.pgmSwitchGpioPin = :pgmPin')->setParameter('pgmPin', $record->getPgmPin(), Types::INTEGER)
				->andWhere('m.powerEnableGpioPin = :powerPin')->setParameter('powerPin', $record->getPowerPin(), Types::INTEGER);
			if ($record->getBaudRate() !== null) {
				$queryBuilder->andWhere('m.baudRate = :baudRate')->setParameter('baudRate', $record->getBaudRate(), Types::INTEGER);
			}
			if ($record->getI2cPin() !== null) {
				$queryBuilder->andWhere('m.i2cEnableGpioPin = :i2cPin')->setParameter('i2cPin', $record->getI2cPin(), Types::INTEGER);
			}
			if ($record->getSpiPin() !== null) {
				$queryBuilder->andWhere('m.spiEnableGpioPin = :spiPin')->setParameter('spiPin', $record->getSpiPin(), Types::INTEGER);
			}
			if ($record->getUartPin() !== null) {
				$queryBuilder->andWhere('m.uartEnableGpioPin = :uartPin')->setParameter('uartPin', $record->getUartPin(), Types::INTEGER);
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
		return 1;
	}
}
