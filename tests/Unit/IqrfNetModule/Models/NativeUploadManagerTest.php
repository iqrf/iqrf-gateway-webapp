<?php

/**
 * TEST: App\IqrfNetModule\Models\SecurityManager
 * @covers App\IqrfNetModule\Models\SecurityManager
 * @phpVersion >= 7.1
 * @testCase
 */


declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Models;

use App\ConfigModule\Models\GenericManager;
use App\IqrfNetModule\Enums\UploadFormats;
use App\IqrfNetModule\Models\NativeUploadManager;
use App\IqrfNetModule\Models\UploadManager;
use Mockery;
use Mockery\MockInterface;
use Nette\Http\FileUpload;
use Tester\Assert;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Test for IQRF TR native upload manager
 */
class NativeUploadManagerTest extends WebSocketTestCase {

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
	private const UPLOAD_PATH = __DIR__ . '/../../../temp/upload';

	/**
	 * @var NativeUploadManager IQRF TR native upload manager
	 */
	private $manager;

	/**
	 * @var UploadManager|MockInterface IQMESH upload manager
	 */
	private $uploadManager;

	/**
	 * Mocks HEX file upload
	 * @return FileUpload Mocked HEX file upload
	 */
	private function mockHexFile(): FileUpload {
		$path = self::DATA_PATH . self::FILENAMES['hex'];
		$value = [
			'name' => self::FILENAMES['hex'],
			'type' => 'text/plain',
			'tmp_name' => $path,
			'error' => UPLOAD_ERR_OK,
			'size' => filesize($path),
		];
		return new FileUpload($value);
	}

	/**
	 * Mocks HEX file upload
	 * @return FileUpload Mocked HEX file upload
	 */
	private function mockIqrfFile(): FileUpload {
		$path = self::DATA_PATH . self::FILENAMES['iqrf'];
		$value = [
			'name' => self::FILENAMES['iqrf'],
			'type' => 'text/plain',
			'tmp_name' => $path,
			'error' => UPLOAD_ERR_OK,
			'size' => filesize($path),
		];
		return new FileUpload($value);
	}

	/**
	 * Tests the function to upload the file into IQRF TR module (HEX file format)
	 */
	public function testUploadHex(): void {
		$this->uploadManager->shouldReceive('upload')
			->with(self::FILENAMES['hex'], UploadFormats::HEX());
		Assert::noError(function (): void {
			$this->manager->upload($this->mockHexFile());
		});
	}

	/**
	 * Tests the function to upload the file into IQRF TR module (IQRF file format)
	 */
	public function testUploadIqrf(): void {
		$this->uploadManager->shouldReceive('upload')
			->with(self::FILENAMES['iqrf'], UploadFormats::IQRF());
		Assert::noError(function (): void {
			$this->manager->upload($this->mockIqrfFile());
		});
	}

	/**
	 * Tests the function to upload the file into IQRF TR module (HEX file format)
	 */
	public function testUploadFileHex(): void {
		$this->uploadManager->shouldReceive('upload')
			->with(self::FILENAMES['hex'], UploadFormats::HEX());
		Assert::noError(function (): void {
			$this->manager->uploadFile(self::DATA_PATH . self::FILENAMES['hex']);
		});
	}

	/**
	 * Tests the function to upload the file into IQRF TR module (IQRF file format)
	 */
	public function testUploadFileIqrf(): void {
		$this->uploadManager->shouldReceive('upload')
			->with(self::FILENAMES['iqrf'], UploadFormats::IQRF());
		Assert::noError(function (): void {
			$this->manager->uploadFile(self::DATA_PATH . self::FILENAMES['iqrf']);
		});
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$configManager = Mockery::mock(GenericManager::class);
		$configManager->shouldReceive('setComponent')
			->with('iqrf::NativeUploadService');
		$configManager->shouldReceive('list')
			->andReturn([[self::UPLOAD_PATH]]);
		$this->uploadManager = Mockery::mock(UploadManager::class);
		$this->manager = new NativeUploadManager($configManager, $this->uploadManager);
	}

}

$test = new NativeUploadManagerTest();
$test->run();
