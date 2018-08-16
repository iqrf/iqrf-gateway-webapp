<?php

/**
 * TEST: App\GatewayModule\Model\LogManager
 * @covers App\GatewayModule\Model\LogManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\Model;

use App\GatewayModule\Model\LogManager;
use App\Model\FileManager;
use Nette\Application\Responses\FileResponse;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for Gateway info manager
 */
class LogManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var FileManager Text file manager
	 */
	private $fileManager;

	/**
	 * @var string Directory with IQRF Gateway Daemon's logs
	 */
	private $logDir = __DIR__ . '/logs/';

	/**
	 * @var LogManager IQRF Gateway Daemon's log manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Set up test environment
	 */
	public function setUp() {
		$this->fileManager = new FileManager($this->logDir);
		$this->manager = new LogManager($this->logDir);
	}

	/**
	 * Test function to get IQRF Gateway Daemon's log files
	 */
	public function testGetLogFiles() {
		$expected = [
			'2018-08-13-13-37-834' => __DIR__ . '/logs/2018-08-13-13-37-834-iqrf-gateway-daemon.log',
			'2018-08-13-13-37-496' => __DIR__ . '/logs/2018-08-13-13-37-496-iqrf-gateway-daemon.log',
		];
		Assert::same($expected, $this->manager->getLogFiles());
	}

	/**
	 * Test function to load the latest IQRF Gateway Daemon's log
	 */
	public function testLoad() {
		$fileName = '2018-08-13-13-37-834-iqrf-gateway-daemon.log';
		$expected = $this->fileManager->read($fileName);
		Assert::same($expected, $this->manager->load());
	}

	/**
	 * Test function to download a ZIP archive with IQRF Gateway Daemon's logs
	 */
	public function testDownload() {
		$path = '/tmp/iqrf-daemon-gateway-logs.zip';
		$fileName = 'iqrf-gateway-daemon-logs' . (new \DateTime())->format('c') . '.zip';
		$contentType = 'application/zip';
		$expected = new FileResponse($path, $fileName, $contentType, true);
		Assert::equal($expected, $this->manager->download());
	}

}

$test = new LogManagerTest($container);
$test->run();
