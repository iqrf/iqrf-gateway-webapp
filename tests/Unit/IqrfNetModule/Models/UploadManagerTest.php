<?php
/**
 * TEST: App\IqrfNetModule\Models\UploadManager
 * @covers App\IqrfNetModule\Models\UploadManager
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Models;

use App\IqrfNetModule\Enums\UploadFormats;
use App\IqrfNetModule\Models\UploadManager;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Test for IQMESH Upload manager
 */
class UploadManagerTest extends WebSocketTestCase {

	/**
	 * @var string File name to
	 */
	private $fileName = 'DPA-Coordinator-SPI-7xD-V403-190612.iqrf';

	/**
	 * @var UploadManager IQRF TR native upload manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new UploadManager($this->request, $this->wsClient);
	}

	/**
	 * Tests the function to read TR configuration (without specified target)
	 */
	public function testUpload(): void {
		$request = [
			'mType' => 'mngDaemon_Upload',
			'data' => [
				'req' => [
					'fileName' => $this->fileName,
				],
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->upload($this->fileName);
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
					'fileName' => $this->fileName,
					'target' => 'iqrf',
				],
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->upload($this->fileName, UploadFormats::IQRF());
		});
	}

}

$test = new UploadManagerTest();
$test->run();
