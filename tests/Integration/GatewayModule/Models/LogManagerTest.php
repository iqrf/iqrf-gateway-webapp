<?php

/**
 * TEST: App\GatewayModule\Models\LogManager
 * @covers App\GatewayModule\Models\LogManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Integration\GatewayModule\Models;

use App\CoreModule\Models\FileManager;
use App\CoreModule\Models\ZipArchiveManager;
use App\GatewayModule\Models\LogManager;
use DateTime;
use Nette\Application\Responses\FileResponse;
use Tester\Assert;
use Tester\TestCase;
use ZipArchive;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Gateway info manager
 */
class LogManagerTest extends TestCase {

	/**
	 * @var FileManager Text file manager
	 */
	private $fileManager;

	/**
	 * @var string Directory with IQRF Gateway Daemon's logs
	 */
	private $logDir;

	/**
	 * @var LogManager IQRF Gateway Daemon's log manager
	 */
	private $manager;

	/**
	 * Tests the function to load the latest IQRF Gateway Daemon's log
	 */
	public function testLoad(): void {
		$fileName = '2018-08-13-13-37-834-iqrf-gateway-daemon.log';
		$expected = $this->fileManager->read($fileName);
		Assert::same($expected, $this->manager->load());
	}

	/**
	 * Tests the function to download a ZIP archive with IQRF Gateway Daemon's logs
	 */
	public function testDownload(): void {
		$actual = $this->manager->download();
		$path = '/tmp/iqrf-daemon-gateway-logs.zip';
		$fileName = 'iqrf-gateway-daemon-logs' . (new DateTime())->format('c') . '.zip';
		$contentType = 'application/zip';
		$expected = new FileResponse($path, $fileName, $contentType, true);
		Assert::equal($expected, $actual);
		$zipManager = new ZipArchiveManager($path, ZipArchive::CREATE);
		$logs = [
			'2018-08-13-13-37-834-iqrf-gateway-daemon.log',
			'2018-08-13-13-37-496-iqrf-gateway-daemon.log',
		];
		foreach ($logs as $log) {
			$expectedLog = $this->fileManager->read($log);
			Assert::same($expectedLog, $zipManager->openFile($log));
		}
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->logDir = realpath(__DIR__ . '/../../../data/logs/');
		$this->fileManager = new FileManager($this->logDir);
		$this->manager = new LogManager($this->logDir);
	}

}

$test = new LogManagerTest();
$test->run();
