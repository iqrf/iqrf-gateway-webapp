<?php
/**
 * TEST: App\IqrfNetModule\Models\UploadManager
 * @covers App\IqrfNetModule\Models\UploadManager
 * @phpVersion >= 7.2
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Models;

use App\IqrfNetModule\Enums\UploadFormats;
use App\IqrfNetModule\Models\NativeUploadManager;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Test for IQRF TR Native upload manager
 */
final class NativeUploadManagerTest extends WebSocketTestCase {

	/**
	 * File name to upload
	 */
	private const FILE_NAME = 'DPA-Coordinator-SPI-7xD-V403-190612.iqrf';

	/**
	 * @var NativeUploadManager IQRF TR native upload manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new NativeUploadManager($this->request, $this->wsClient);
	}

	/**
	 * Tests the function to read TR configuration (without specified target)
	 */
	public function testUpload(): void {
		$request = [
			'mType' => 'mngDaemon_Upload',
			'data' => [
				'req' => [
					'fileName' => self::FILE_NAME,
				],
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->upload(self::FILE_NAME);
		});
	}

	/**
	 * Tests the function to read TR configuration (with specified target)
	 */
	public function testUploadToTarget(): void {
		$request = [
			'mType' => 'mngDaemon_Upload',
			'data' => [
				'req' => [
					'fileName' => self::FILE_NAME,
					'target' => 'iqrf',
				],
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->upload(self::FILE_NAME, UploadFormats::IQRF());
		});
	}

}

$test = new NativeUploadManagerTest();
$test->run();
