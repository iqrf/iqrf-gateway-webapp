<?php

/**
 * TEST: App\ConfigModule\Models\IqrfManager
 * @covers App\ConfigModule\Models\IqrfManager
 * @phpVersion >= 7.3
 * @testCase
 */
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

namespace Tests\Unit\ConfigModule\Models;

use App\ConfigModule\Models\IqrfManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for IQRF interface manager
 */
final class IqrfManagerTest extends CommandTestCase {

	/**
	 * @var IqrfManager IQRF interface manager
	 */
	private $manager;

	/**
	 * Tests the function to get list of USB CDC interfaces available in the system
	 */
	public function testGetCdcInterfaces(): void {
		$command = 'ls -1 /dev/ttyACM*';
		$expected = ['/dev/ttyACM0', '/dev/ttyACM1'];
		$output = implode(PHP_EOL, $expected);
		$this->receiveCommand($command, true, $output);
		Assert::same($expected, $this->manager->getCdcInterfaces());
	}

	/**
	 * Tests the function to get list of SPI interfaces available in the system
	 */
	public function testGetSpiInterfaces(): void {
		$command = 'ls -1 /dev/spidev*';
		$expected = ['/dev/spidev0.0', '/dev/spidev0.1', '/dev/spidev1.0', '/dev/spidev1.1'];
		$output = implode(PHP_EOL, $expected);
		$this->receiveCommand($command, true, $output);
		Assert::same($expected, $this->manager->getSpiInterfaces());
	}

	/**
	 * Tests the function to get list of UART interfaces available in the system
	 */
	public function testGetUartInterfaces(): void {
		$command = 'ls -1 /dev/ttyAMA* /dev/ttyS*';
		$expected = ['/dev/ttyS0', '/dev/ttyS1', '/dev/ttyS2', '/dev/ttyS3'];
		$output = implode(PHP_EOL, $expected);
		$this->receiveCommand($command, true, $output);
		Assert::same($expected, $this->manager->getUartInterfaces());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new IqrfManager($this->commandManager);
	}

}

$test = new IqrfManagerTest();
$test->run();
