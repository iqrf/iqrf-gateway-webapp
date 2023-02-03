<?php

/**
 * TEST: App\CoreModule\Models\FeatureManager
 * @covers App\CoreModule\Models\FeatureManager
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

namespace Tests\Integration\CoreModule\Models;

use App\CoreModule\Exceptions\FeatureNotFoundException;
use App\CoreModule\Models\FeatureManager;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for optional feature manager
 */
final class FeatureManagerTest extends TestCase {

	/**
	 * @var string Path to the temporary file
	 */
	private const PATH_TEMP = TMP_DIR . '/features.neon';

	/**
	 * @var string Path to the original file
	 */
	private const PATH = TESTER_DIR . '/data/features.neon';

	/**
	 * @var FeatureManager Optional feature manager
	 */
	private FeatureManager $manager;

	/**
	 * @var FeatureManager Optional feature manager
	 */
	private FeatureManager $managerTemp;

	/**
	 * Copies the original file
	 */
	private function copy(): void {
		FileSystem::copy(self::PATH, self::PATH_TEMP);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->manager = new FeatureManager(self::PATH);
		$this->managerTemp = new FeatureManager(self::PATH_TEMP);
	}

	/**
	 * Tests the constructor with nonexistent path
	 */
	public function testConstructorNonexistent(): void {
		$manager = new FeatureManager(TMP_DIR . '/nonsense');
		Assert::same(['docs'], $manager->listEnabled());
	}

	/**
	 * Tests the function to edit feature configuration
	 */
	public function testEdit(): void {
		Environment::lock('feature', TMP_DIR);
		$this->copy();
		$expected = [
			'enabled' => true,
			'url' => '/grafana/',
		];
		Assert::noError(function () use ($expected): void {
			$this->managerTemp->edit('grafana', $expected);
		});
		Assert::same($expected, $this->managerTemp->get('grafana'));
	}

	/**
	 * Tests the function to edit feature configuration (nonexistent feature)
	 */
	public function testEditNotFound(): void {
		Assert::exception(function (): void {
			$this->manager->edit('nonsense', ['enabled' => true]);
		}, FeatureNotFoundException::class);
	}

	/**
	 * Tests the function to get optional feature configuration
	 */
	public function testGet(): void {
		$expected = [
			'enabled' => false,
			'url' => '/grafana/',
		];
		Assert::same($expected, $this->manager->get('grafana'));
	}

	/**
	 * Tests the function to check if the feature is enabled
	 */
	public function testIsEnabled(): void {
		Assert::true($this->manager->isEnabled('docs'));
		Assert::exception(function (): void {
			$this->manager->isEnabled('nonsence');
		}, FeatureNotFoundException::class);
	}

	/**
	 * Tests the function to list enabled features
	 */
	public function testListEnabled(): void {
		$expected = ['docs'];
		Assert::same($expected, $this->manager->listEnabled());
	}

	/**
	 * Tests the function to set feature enablement
	 */
	public function testSetEnabled(): void {
		Environment::lock('feature', TMP_DIR);
		$this->copy();
		$expected = ['docs'];
		Assert::same($expected, $this->managerTemp->listEnabled());
		$this->managerTemp->setEnabled(['ssh'], true);
		$expected = ['docs', 'ssh'];
		Assert::same($expected, $this->managerTemp->listEnabled());
	}

	/**
	 * Tests the function to set feature enablement (nonexistent feature)
	 */
	public function testSetEnabledNotFound(): void {
		Assert::exception(function (): void {
			$this->manager->setEnabled(['nonsense'], true);
		}, FeatureNotFoundException::class);
	}

}

$test = new FeatureManagerTest();
$test->run();
