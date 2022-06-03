<?php

/**
 * TEST: App\MaintenanceModule\Models\PixlaManager
 * @covers App\MaintenanceModule\Models\PixlaManager
 * @phpVersion >= 7.4
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

namespace Tests\Unit\MaintenanceModule\Models;

use App\CoreModule\Models\FileManager;
use App\MaintenanceModule\Models\PixlaManager;
use Mockery;
use Mockery\MockInterface;
use Nette\IOException;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for PIXLA management system manager
 */
final class PixlaManagerTest extends CommandTestCase {

	/**
	 * @var string File containing PIXLA token
	 */
	private const FILE_NAME = 'customer_id';

	/**
	 * @var string PIXLA token
	 */
	private const TOKEN = 'pixla-token';

	/**
	 * @var string PIXLA new token
	 */
	private const NEW_TOKEN = 'pixla-new-token';

	/**
	 * @var FileManager|MockInterface File manager
	 */
	private $fileManager;

	/**
	 * @var PixlaManager PIXLA management system manager
	 */
	private PixlaManager $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->fileManager = Mockery::mock(FileManager::class);
		$this->manager = new PixlaManager($this->fileManager);
	}

	/**
	 * Tests the function to get PIXLA token (success)
	 */
	public function testGetTokenSuccess(): void {
		$this->fileManager->shouldReceive('read')
			->withArgs([self::FILE_NAME])
			->andReturn(self::TOKEN);
		Assert::same(self::TOKEN, $this->manager->getToken());
	}

	/**
	 * Tests the function to get PIXLA token (failure)
	 */
	public function testGetTokenFailure(): void {
		$this->fileManager->shouldReceive('read')
			->withArgs([self::FILE_NAME])
			->andThrow(IOException::class);
		Assert::null($this->manager->getToken());
	}

	/**
	 * Tests the function to set PIXLA token (success)
	 */
	public function testSetTokenSuccess(): void {
		$this->fileManager->shouldReceive('write')
			->withArgs([self::FILE_NAME, self::NEW_TOKEN]);
		Assert::noError(function (): void {
			$this->manager->setToken(self::NEW_TOKEN);
		});
	}

	/**
	 * Tests the function to set PIXLa token (failure)
	 */
	public function testSetTokenFailure(): void {
		$this->fileManager->shouldReceive('write')
			->withArgs([self::FILE_NAME, self::NEW_TOKEN])
			->andThrow(IOException::class);
		Assert::exception(function (): void {
			$this->manager->setToken(self::NEW_TOKEN);
		}, IOException::class);
	}

}

$test = new PixlaManagerTest();
$test->run();
