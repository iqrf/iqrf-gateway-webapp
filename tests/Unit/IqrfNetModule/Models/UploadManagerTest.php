<?php

/**
 * TEST: App\IqrfNetModule\Models\UploadManager
 * @covers App\IqrfNetModule\Models\UploadManager
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

namespace Tests\Unit\IqrfNetModule\Models;

use App\ConfigModule\Models\GenericManager;
use App\IqrfNetModule\Models\UploadManager;
use Mockery;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Test for IQRF TR upload manager
 */
final class UploadManagerTest extends WebSocketTestCase {

	/**
	 * Data dir path
	 */
	private const DATA_PATH = __DIR__ . '/../../../data/upload/';

	/**
	 * File names
	 */
	private const FILENAMES = [
		'hex' => 'CustomDpaHandler-Coordinator-FRCandSleep-7xD-V403-190612.hex',
		'iqrf' => 'DPA-Coordinator-SPI-7xD-V403-190612.iqrf',
	];

	/**
	 * Upload directory path
	 */
	private const UPLOAD_PATH = __DIR__ . '/../../../temp/upload/';

	/**
	 * @var UploadManager IQRF TR upload manager
	 */
	private $manager;

	/**
	 * Tests the function to upload the file into IQRF TR module (HEX file format)
	 */
	public function testUploadFileHex(): void {
		$expected = ['fileName' => self::FILENAMES['hex'], 'format' => 'hex'];
		$fileContent = FileSystem::read(self::DATA_PATH . self::FILENAMES['hex']);
		$actual = $this->manager->uploadFile(self::FILENAMES['hex'], $fileContent);
		Assert::equal($expected, $actual);
		Assert::equal($fileContent, FileSystem::read(self::UPLOAD_PATH . self::FILENAMES['hex']));
	}

	/**
	 * Tests the function to upload the file into IQRF TR module (IQRF file format)
	 */
	public function testUploadFileIqrf(): void {
		$expected = ['fileName' => self::FILENAMES['iqrf'], 'format' => 'iqrf'];
		$fileContent = FileSystem::read(self::DATA_PATH . self::FILENAMES['iqrf']);
		$actual = $this->manager->uploadFile(self::FILENAMES['iqrf'], $fileContent);
		Assert::equal($expected, $actual);
		Assert::equal($fileContent, FileSystem::read(self::UPLOAD_PATH . self::FILENAMES['iqrf']));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$configManager = Mockery::mock(GenericManager::class);
		$configManager->shouldReceive('setComponent')
			->with('iqrf::NativeUploadService');
		$configManager->shouldReceive('list')
			->andReturn([['uploadPath' => self::UPLOAD_PATH]]);
		$this->manager = new UploadManager($configManager);
	}

}

$test = new UploadManagerTest();
$test->run();
