<?php

declare(strict_types = 1);

namespace Tests\Toolkit\TestCases;

use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\JsonFileManager;
use Mockery;
use Nette\Utils\JsonException;
use Tester\TestCase;

/**
 * JSON configuration test case
 */
abstract class DpaParserTestCase extends TestCase {

	/**
	 * @var JsonFileManager JSON file manager
	 */
	protected $fileManager;

	/**
	 * Reads the DPA packet from JSON DPA response
	 * @param string $fileName Name of JSON file with DPA response
	 * @return string|null DPA response packet
	 */
	protected function readResponsePacket(string $fileName): ?string {
		try {
			$content = $this->fileManager->read($fileName);
		} catch (JsonException $e) {
			return null;
		}
		return $content['data']['rsp']['rData'] ?? null;
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$data = __DIR__ . '/../../data/iqrf/';
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$this->fileManager = new JsonFileManager($data, $commandManager);
	}

	/**
	 * Cleanups the test environment
	 */
	protected function tearDown(): void {
		Mockery::close();
	}

}
