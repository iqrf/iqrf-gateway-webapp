<?php

/**
 * TEST: App\InstallModule\Models\SudoManager
 * @covers App\InstallModule\Models\SudoManager
 * @phpVersion >= 7.3
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\InstallModule\Models;

use App\InstallModule\Models\PhpModuleManager;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for PHP modules manager
 */
final class PhpModuleManagerTest extends TestCase {

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
	}

	/**
	 * Tests the function to check installed and loaded PHP modules
	 */
	public function testCheckModules(): void {
		Assert::noError(function (): void {
			PhpModuleManager::checkModules();
		});
	}

}

$test = new PhpModuleManagerTest();
$test->run();
