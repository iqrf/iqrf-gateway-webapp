<?php

/**
 * TEST: App\IqrfNetModule\Models\UploadManager
 * @covers App\IqrfNetModule\Models\UploadManager
 * @phpVersion >= 7.2
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
	private const UPLOAD_PATH = __DIR__ . '/../../../temp/upload';

	/**
	 * @var UploadManager IQRF TR upload manager
	 */
	private $manager;

	/**
	 * @var NativeUploadManager|MockInterface IQMESH upload manager
	 */
	private $uploadManager;

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
		$this->manager = new UploadManager($configManager);
	}

}

$test = new UploadManagerTest();
$test->run();
