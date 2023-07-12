<?php

/**
 * TEST: App\CoreModule\Models\PrivilegedFileManager
 * @covers App\CoreModule\Models\PrivilegedFileManager
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

namespace Tests\Unit\CoreModule\Models;

use App\CoreModule\Models\PrivilegedFileManager;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for privileged text file manager
 */
final class PrivilegedFileManagerTest extends CommandTestCase {

	/**
	 * File name
	 */
	private const FILE_NAME = 'config.json';

	/**
	 * Directory with configuration files
	 */
	private const CONFIG_PATH = TESTER_DIR . '/data/configuration/';

	/**
	 * @var PrivilegedFileManager Text file manager
	 */
	private PrivilegedFileManager $manager;

	/**
	 * Tests the function to get a base path
	 */
	public function testGetBasePath(): void {
		Assert::same(self::CONFIG_PATH, $this->manager->getBasePath() . '/');
	}

	/**
	 * Tests the function to change permissions
	 */
	public function testChmod(): void {
		$this->receiveCommand('chmod 777 \'' . self::CONFIG_PATH . self::FILE_NAME . '\'', true);
		Assert::noError(function (): void {
			$this->manager->chmod(self::FILE_NAME, 0777);
		});
	}

	/**
	 * Tests the function to change permissions (nonexistent file)
	 */
	public function testChmodNonexistent(): void {
		$stderr = 'chmod: cannot access \'' . self::CONFIG_PATH . self::FILE_NAME . '\': No such file or directory';
		$this->receiveCommand('chmod 755 \'' . self::CONFIG_PATH . self::FILE_NAME . '\'', true, '', $stderr, 1);
		Assert::throws(function (): void {
			$this->manager->chmod(self::FILE_NAME, 0755);
		}, IOException::class, $stderr);
	}

	/**
	 * Tests the function to change owner
	 */
	public function testChown(): void {
		$this->receiveCommand('chown \'root:root\' \'' . self::CONFIG_PATH . self::FILE_NAME . '\'', true);
		Assert::noError(function (): void {
			$this->manager->chown(self::FILE_NAME, 'root', 'root');
		});
	}

	/**
	 * Tests the function to change owner (nonexistent file)
	 */
	public function testChownNonexistent(): void {
		$stderr = 'chown: cannot access \'' . self::CONFIG_PATH . self::FILE_NAME . '\': No such file or directory';
		$this->receiveCommand('chown \'root:root\' \'' . self::CONFIG_PATH . self::FILE_NAME . '\'', true, '', $stderr, 1);
		Assert::throws(function (): void {
			$this->manager->chown(self::FILE_NAME, 'root', 'root');
		}, IOException::class, $stderr);
	}

	/**
	 * Tests the function to recursively change owner
	 */
	public function testChownRecursive(): void {
		$this->receiveCommand('chown -R \'root:root\' \'' . self::CONFIG_PATH . self::FILE_NAME . '\'', true);
		Assert::noError(function (): void {
			$this->manager->chown(self::FILE_NAME, 'root', 'root', true);
		});
	}

	/**
	 * Tests the function to read a text file
	 */
	public function testRead(): void {
		$expected = FileSystem::read(self::CONFIG_PATH . self::FILE_NAME);
		$this->receiveCommand('cat \'' . self::CONFIG_PATH . self::FILE_NAME . '\'', true, $expected);
		Assert::equal($expected, $this->manager->read(self::FILE_NAME));
	}

	/**
	 * Tests the function to read a text file (nonexistent file)
	 */
	public function testReadNonexistent(): void {
		$stderr = 'cat: \'' . self::CONFIG_PATH . self::FILE_NAME . '\': No such file or directory';
		$this->receiveCommand('cat \'' . self::CONFIG_PATH . self::FILE_NAME . '\'', true, '', $stderr, 1);
		Assert::throws(function (): void {
			$this->manager->read(self::FILE_NAME);
		}, IOException::class, $stderr);
	}

	/**
	 * Tests the function to read a JSON file
	 */
	public function testReadJson(): void {
		$content = FileSystem::read(self::CONFIG_PATH . self::FILE_NAME);
		$this->receiveCommand('cat \'' . self::CONFIG_PATH . self::FILE_NAME . '\'', true, $content);
		Assert::equal(Json::decode($content, forceArrays: true), $this->manager->readJson(self::FILE_NAME));
	}

	/**
	 * Tests the function to read a JSON file (nonexistent file)
	 */
	public function testReadJsonNonexistent(): void {
		$stderr = 'cat: \'' . self::CONFIG_PATH . self::FILE_NAME . '\': No such file or directory';
		$this->receiveCommand('cat \'' . self::CONFIG_PATH . self::FILE_NAME . '\'', true, '', $stderr, 1);
		Assert::throws(function (): void {
			$this->manager->readJson(self::FILE_NAME);
		}, IOException::class, $stderr);
	}

	/**
	 * Tests the function to delete a file
	 */
	public function testDelete(): void {
		$this->receiveCommand('rm -rf \'' . self::CONFIG_PATH . self::FILE_NAME . '\'', true);
		Assert::noError(function (): void {
			$this->manager->delete(self::FILE_NAME);
		});
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new PrivilegedFileManager(self::CONFIG_PATH, $this->commandManager);
	}

}

$test = new PrivilegedFileManagerTest();
$test->run();
