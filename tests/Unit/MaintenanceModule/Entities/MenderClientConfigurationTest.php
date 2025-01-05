<?php

/**
 * TEST: App\MaintenanceModule\Entities\MenderClientConfiguration
 * @covers App\MaintenanceModule\Entities\MenderClientConfiguration
 * @phpVersion >= 8.1
 * @testCase
 */
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

namespace Tests\Unit\MaintenanceModule\Entities;

use App\MaintenanceModule\Entities\MenderClientConfiguration;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Mender client configuration entity
 */
final class MenderClientConfigurationTest extends TestCase {

	/**
	 * @var array<'3'|'3-4'|'4', array<string, mixed>> $configs Mender client configurations
	 */
	private array $configs = [];

	/**
	 * @var array<'3'|'4', MenderClientConfiguration> $entities Mender client configuration entities
	 */
	private array $entities = [];

	/**
	 * @var array<'3'|'4', array{config: array<string, mixed>, version: int}> $jsons JSON Mender client configurations
	 */
	private array $jsons = [];

	/**
	 * Sets up the test environment
	 */
	public function __construct() {
		foreach (['3', '3-4', '4'] as $version) {
			$this->configs[$version] = Json::decode(FileSystem::read(TESTER_DIR . '/data/maintenance/mender/client' . $version . '/mender.conf'), Json::FORCE_ARRAY);
		}
		foreach (['3', '4'] as $version) {
			$this->entities[$version] = new MenderClientConfiguration(
				(int) $version,
				['https://generic.mender.iqrf.org'],
				'',
				'dummy',
				1800,
				28800,
				300,
			);
			$this->jsons[$version] = Json::decode(FileSystem::read(TESTER_DIR . '/data/maintenance/mender/client' . $version . '/mender.json'), Json::FORCE_ARRAY);
		}
	}

	/**
	 * Tests the function to deserialize Mender client configuration from configuration file
	 * @param int $version Mender client major version
	 * @param array<string, mixed> $jsonConfig JSON Mender client configuration
	 * @param MenderClientConfiguration $entity Mender client configuration entity
	 * @dataProvider getConfigDeserializeData
	 */
	public function testConfigDeserialize(int $version, array $jsonConfig, MenderClientConfiguration $entity): void {
		$deserialized = MenderClientConfiguration::configDeserialize($version, $jsonConfig);
		Assert::equal($entity, $deserialized);
	}

	/**
	 * Tests the function to fix up Mender client configuration
	 * @param int $version Mender client major version
	 * @param array<string, mixed> $config Mender client configuration
	 * @param array<string, mixed> $expected Fixed up configuration
	 * @dataProvider getConfigFixupData
	 */
	public function testConfigFixUp(int $version, array $config, array $expected): void {
		Assert::equal($expected, MenderClientConfiguration::configFixUp($version, $config));
	}

	/**
	 * Tests the function to serialize Mender client configuration from configuration file
	 * @param int $version Mender client major version
	 * @param array<string, mixed> $jsonConfig JSON Mender client configuration
	 * @param MenderClientConfiguration $entity Mender client configuration entity
	 * @dataProvider getConfigSerializeData
	 */
	public function testConfigSerialize(int $version, array $jsonConfig, MenderClientConfiguration $entity): void {
		Assert::equal($jsonConfig, $entity->configSerialize());
	}

	/**
	 * Tests the function to deserialize Mender client configuration from JSON
	 * @param array{config: array<string, mixed>, version: int} $jsonConfig JSON serialized Mender client configuration
	 * @param MenderClientConfiguration $entity Mender client configuration entity
	 * @dataProvider getJsonData
	 */
	public function testJsonDeserialize(array $jsonConfig, MenderClientConfiguration $entity): void {
		$deserialized = MenderClientConfiguration::jsonDeserialize($jsonConfig);
		Assert::equal($entity, $deserialized);
	}

	/**
	 * Tests the function to serialize Mender client configuration into JSON
	 * @param array{config: array<string, mixed>, version: int} $jsonConfig JSON serialized Mender client configuration
	 * @param MenderClientConfiguration $entity Mender client configuration entity
	 * @dataProvider getJsonData
	 */
	public function testJsonSerialize(array $jsonConfig, MenderClientConfiguration $entity): void {
		Assert::equal($jsonConfig, $entity->jsonSerialize());
	}

	/**
	 * Returns data for testing the function to deserialize Mender client configuration
	 * @return array<array{int, array<string, mixed>, MenderClientConfiguration}> Configuration deserialization data
	 */
	protected function getConfigDeserializeData(): array {
		return [
			[3, $this->configs['3'], $this->entities['3']],
			[3, $this->configs['3-4'], $this->entities['3']],
			[3, $this->configs['4'], $this->entities['3']],
			[4, $this->configs['3'], $this->entities['4']],
			[4, $this->configs['3-4'], $this->entities['4']],
			[4, $this->configs['4'], $this->entities['4']],
		];
	}

	/**
	 * Returns data for testing the function to fix up Mender client configuration
	 * @return array<array{int, array<string, mixed>, array<string, mixed>}> Configuration fixup data
	 */
	protected function getConfigFixupData(): array {
		return [
			[3, $this->configs['3'], $this->configs['3']],
			[3, $this->configs['3-4'], $this->configs['3']],
			[3, $this->configs['4'], $this->configs['3']],
			[4, $this->configs['3'], $this->configs['4']],
			[4, $this->configs['3-4'], $this->configs['4']],
			[4, $this->configs['4'], $this->configs['4']],
		];
	}

	/**
	 * Returns data for testing the function to serialize Mender client configuration
	 * @return array<array{int, array<string, mixed>, MenderClientConfiguration}> Configuration serialization data
	 */
	protected function getConfigSerializeData(): array {
		return [
			[3, $this->configs['3'], $this->entities['3']],
			[4, $this->configs['4'], $this->entities['4']],
		];
	}

	/**
	 * Returns data for JSON (de)serialization tests
	 * @return array<array{array{config: array<string, mixed>, version: int}, MenderClientConfiguration}> Data for JSON (de)serialization tests
	 */
	protected function getJsonData(): array {
		return [
			[$this->jsons['3'], $this->entities['3']],
			[$this->jsons['4'], $this->entities['4']],
		];
	}

}

$test = new MenderClientConfigurationTest();
$test->run();
