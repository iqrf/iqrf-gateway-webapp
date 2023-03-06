<?php

/**
 * TEST: App\CoreModule\Models\VersionManager
 * @covers App\CoreModule\Models\VersionManager
 * @phpVersion >= 7.4
 * @testCase
 */
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

namespace Tests\Unit\CoreModule\Models;

use App\CoreModule\Models\VersionManager;
use App\GatewayModule\Models\VersionManager as GatewayVersionManager;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\MockInterface;
use Nette\Caching\Storages\DevNullStorage;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for version manager
 */
final class VersionManagerTest extends CommandTestCase {

	/**
	 * @var string Current version of the webapp
	 */
	private const CURRENT_VERSION = '3.0.0-alpha';

	/**
	 * @var string Current stable version of the webapp
	 */
	private const STABLE_VERSION = '2.4.0';

	/**
	 * @var DevNullStorage Cache storage for testing
	 */
	private DevNullStorage $cacheStorage;

	/**
	 * @var GatewayVersionManager|MockInterface Gateway version manager
	 */
	private GatewayVersionManager $gwVersionManager;

	/**
	 * @var VersionManager|MockInterface Version manager
	 */
	private $manager;

	/**
	 * Tests the function to check if an update is available for the webapp
	 */
	public function testAvailableWebappUpdateNo(): void {
		$this->gwVersionManager->shouldReceive('getWebapp')
			->withArgs([false])
			->andReturn(self::CURRENT_VERSION);
		$this->manager->shouldReceive('getCurrentWebapp')
			->withArgs([])->andReturn(self::CURRENT_VERSION);
		Assert::false($this->manager->availableWebappUpdate());
	}

	/**
	 * Tests the function to check if an update is available for the webapp
	 */
	public function testAvailableWebappUpdateYes(): void {
		$this->gwVersionManager->shouldReceive('getWebapp')
			->withArgs([false])
			->andReturn(self::STABLE_VERSION);
		$this->manager->shouldReceive('getCurrentWebapp')
			->withArgs([])->andReturn(self::CURRENT_VERSION);
		Assert::true($this->manager->availableWebappUpdate());
	}

	/**
	 * Tests the function to get the current webapp version (stable - success)
	 */
	public function testGetCurrentWebappStable(): void {
		$responseMock = new MockHandler([
			new Response(200, [], '{"version": "' . self::STABLE_VERSION . '"}'),
		]);
		$handler = HandlerStack::create($responseMock);
		$client = new Client(['handler' => $handler]);
		$manager = new VersionManager($this->cacheStorage, $client, $this->gwVersionManager);
		Assert::same(self::STABLE_VERSION, $manager->getCurrentWebapp());
	}

	/**
	 * Tests the function to get the current webapp version (stable - failure, master - success)
	 */
	public function testGetCurrentWebappMaster(): void {
		$responseMock = new MockHandler([
			new Response(404),
			new Response(200, [], '{"version": "' . self::STABLE_VERSION . '"}'),
		]);
		$handler = HandlerStack::create($responseMock);
		$client = new Client(['handler' => $handler]);
		$manager = new VersionManager($this->cacheStorage, $client, $this->gwVersionManager);
		Assert::same(self::STABLE_VERSION, $manager->getCurrentWebapp());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->cacheStorage = new DevNullStorage();
		$client = new Client();
		$this->gwVersionManager = Mockery::mock(GatewayVersionManager::class);
		$this->manager = Mockery::mock(VersionManager::class, [$this->cacheStorage, $client, $this->gwVersionManager])->makePartial();
	}

}

$test = new VersionManagerTest();
$test->run();
