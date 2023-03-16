<?php

/**
 * TEST: App\IqrfNetModule\Models\UploadManager
 * @covers App\IqrfNetModule\Models\UploadManager
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

namespace Tests\Unit\IqrfNetModule\Models;

use App\ConfigModule\Models\GenericManager;
use App\ConfigModule\Models\MainManager;
use App\IqrfNetModule\Enums\UploadFormats;
use App\IqrfNetModule\Exceptions\UploaderMissingException;
use App\IqrfNetModule\Models\UploadManager;
use Mockery;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Test for IQRF TR upload manager
 */
final class UploadManagerTest extends CommandTestCase {

	/**
	 * @var string Data dir path
	 */
	private const DATA_PATH = TESTER_DIR . '/data/upload/';

	/**
	 * @var array<string, string> File names
	 */
	private const FILENAMES = [
		'hex' => 'CustomDpaHandler-Coordinator-FRCandSleep-7xD-V403-190612.hex',
		'iqrf' => 'DPA-Coordinator-SPI-7xD-V403-190612.iqrf',
	];

	/**
	 * @var string Upload directory pathUpload directory name
	 */
	private const UPLOAD_DIR = '/upload/';

	/**
	 * @var string Upload directory path
	 */
	private const UPLOAD_PATH = TMP_DIR . self::UPLOAD_DIR;

	/**
	 * @var UploadManager IQRF TR upload manager
	 */
	private UploadManager $manager;

	/**
	 * Returns file formats
	 * @return array<array<string>> File formats
	 */
	public function getUploadFileFormats(): array {
		return [['hex'], ['iqrf']];
	}

	/**
	 * Tests the function to upload the file into IQRF TR module (HEX file format)
	 * @dataProvider getUploadFileFormats
	 */
	public function testUploadFile(string $format): void {
		$expected = ['fileName' => self::FILENAMES[$format], 'format' => $format];
		$fileContent = FileSystem::read(self::DATA_PATH . '/' . self::FILENAMES[$format]);
		$actual = $this->manager->uploadToFs(self::FILENAMES[$format], $fileContent);
		Assert::equal($expected, $actual);
		Assert::equal($fileContent, FileSystem::read(self::UPLOAD_PATH . self::FILENAMES[$format]));
	}

	/**
	 * Returns file name and format
	 * @return array<array<string|UploadFormats|bool>> File name and format
	 */
	public function getUploadToTrData(): array {
		return [
			['ChangeOS-TR7xD-405(08D7)-406(08D8).iqrf', UploadFormats::IQRF, true],
			['0402_0002_DDC-SE+RE.hex', UploadFormats::HEX, false],
		];
	}

	/**
	 * Tests the function to upload the file into the transceiver
	 * @dataProvider getUploadToTrData
	 * @param string $fileName File name
	 * @param UploadFormats $format File format
	 * @param bool $os Uploaded file is OS patch
	 */
	public function testUploadToTr(string $fileName, UploadFormats $format, bool $os): void {
		$this->receiveCommandExist('iqrf-gateway-uploader', true);
		$command = sprintf('iqrf-gateway-uploader %s \'%s\'', $format->getUploaderParameter(), ($os ? UploadManager::OS_PATH : self::UPLOAD_PATH) . $fileName);
		$this->receiveCommand($command, true);
		Assert::noError(function () use ($fileName, $os, $format): void {
			$this->manager->uploadToTr($fileName, $os, $format);
		});
	}

	/**
	 * Tests the function to upload the file into the transceiver (missing IQRF Gateway Uploader)
	 */
	public function testUploadToTrWithoutUploader(): void {
		$this->receiveCommandExist('iqrf-gateway-uploader', false);
		Assert::exception(function (): void {
			$this->manager->uploadToTr('test.iqrf');
		}, UploaderMissingException::class, 'IQRF Gateway Uploader is not installed.');
	}


	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$mainManager = Mockery::mock(MainManager::class);
		$mainManager->shouldReceive('getCacheDir')
			->andReturn(TMP_DIR);
		$configManager = Mockery::mock(GenericManager::class);
		$configManager->shouldReceive('setComponent')
			->with('iqrf::OtaUploadService');
		$configManager->shouldReceive('list')
			->andReturn([['uploadPathSuffix' => self::UPLOAD_DIR]]);
		$this->manager = new UploadManager($this->commandManager, $configManager, $mainManager);
	}

}

$test = new UploadManagerTest();
$test->run();
