<?php

/**
 * TEST: App\GatewayModule\Models\JournalReaderManager
 * @covers App\GatewayModule\Models\JournalReaderManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

namespace Tests\Unit\GatewayModule\Models;

use App\GatewayModule\Exceptions\JournalReaderArgumentException;
use App\GatewayModule\Exceptions\JournalReaderInternalException;
use App\GatewayModule\Models\JournalReaderManager;
use Iqrf\CommandExecutor\CommandExecutor;
use Iqrf\CommandExecutor\CommandStack;
use Iqrf\CommandExecutor\Tester\Traits\CommandExecutorTestCase;
use Iqrf\FileManager\FileManager;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Test for mini-journalreader manager
 */
final class JournalReaderManagerTest extends TestCase {

	use CommandExecutorTestCase;

	/**
	 * Path to directory with testing data
	 */
	private const DATA_DIR = TESTER_DIR . '/data/journal/';

	/**
	 * IQRF Journal Reader invocation command
	 */
	private const READER_COMMAND = 'iqrf-journal-reader';

	/**
	 * Commands
	 */
	private const COMMANDS = [
		'cursorless' => 'iqrf-journal-reader -j -n 10',
		'cursor' => 'iqrf-journal-reader -j -n 10 -e \'s=c898cdeb1833489094a2e5f158e28858;i=39782bc;b=e38555478f2e49389c854801d0aa15c5;m=14855146;t=5ebca2f015ad3;x=bd0404fdde8c58c6\'',
		'invalidCursor' => 'iqrf-journal-reader -j -n 10 -e \'invalid\'',
		'internalError' => 'iqrf-journal-reader -j -n 10 -e \'s=c898cdeb1833489094a2e5f158e28858;i=39782bc;b=e38555478f2e49389c854801d0aa15c5;m=14855146;t=5ebca2f015ad3;x=bd0404fdde8c58c6\'',
	];

	/**
	 * @var FileManager $fileManager JSON file manager
	 */
	private FileManager $fileManager;

	/**
	 * @var JournalReaderManager $journalManager Journal manager
	 */
	private JournalReaderManager $journalManager;

	/**
	 * Tests the function to get journal records without a cursor
	 */
	public function testGetRecordsCursorLess(): void {
		$raw = $this->fileManager->readJson('cursorless.json');
		$parsed = $this->fileManager->readJson('cursorless_parsed.json');
		$this->receiveCommandExist(self::READER_COMMAND, true);
		$this->receiveCommand(
			command: self::COMMANDS['cursorless'],
			needSudo: true,
			stdout: Json::encode($raw),
		);
		Assert::equal($parsed, $this->journalManager->getRecords(10));
	}

	/**
	 * Tests the function to get journal records with a cursor
	 */
	public function testGetRecordsCursor(): void {
		$raw = $this->fileManager->readJson('cursor.json');
		$parsed = $this->fileManager->readJson('cursor_parsed.json');
		$this->receiveCommandExist(self::READER_COMMAND, true);
		$this->receiveCommand(
			command: self::COMMANDS['cursor'],
			needSudo: true,
			stdout: Json::encode($raw),
		);
		Assert::equal($parsed, $this->journalManager->getRecords(10, 's=c898cdeb1833489094a2e5f158e28858;i=39782bc;b=e38555478f2e49389c854801d0aa15c5;m=14855146;t=5ebca2f015ad3;x=bd0404fdde8c58c6'));
	}

	/**
	 * Tests the function to get journal records without the journal reader utility installed
	 */
	public function testGetRecordsReaderMissing(): void {
		$this->receiveCommandExist(self::READER_COMMAND, false);
		Assert::exception(function (): void {
			$this->journalManager->getRecords(10);
		}, JournalReaderInternalException::class, 'IQRF Journal Reader is not installed.');
	}

	/**
	 * Tests the function to get journal records with invalid cursor
	 */
	public function testGetRecordsError(): void {
		$this->receiveCommandExist(
			command: self::READER_COMMAND,
			output: true,
			count: 2,
		);
		$this->receiveCommand(
			command: self::COMMANDS['invalidCursor'],
			needSudo: true,
			stderr: 'Invalid cursor format: invalid',
			exitCode: 1,
		);
		$this->receiveCommand(
			command: self::COMMANDS['internalError'],
			needSudo: true,
			stderr: 'Failed to get record cursor: Cannot assign requested address',
			exitCode: 2,
		);
		Assert::exception(function (): void {
			$this->journalManager->getRecords(10, 'invalid');
		}, JournalReaderArgumentException::class, 'Invalid cursor format: invalid');
		Assert::exception(function (): void {
			$this->journalManager->getRecords(10, 's=c898cdeb1833489094a2e5f158e28858;i=39782bc;b=e38555478f2e49389c854801d0aa15c5;m=14855146;t=5ebca2f015ad3;x=bd0404fdde8c58c6');
		}, JournalReaderInternalException::class, 'Failed to get record cursor: Cannot assign requested address');
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->setUpCommandExecutor();
		$commandStack = new CommandStack();
		$commandManager = new CommandExecutor(true, $commandStack);
		$this->fileManager = new FileManager(self::DATA_DIR, $commandManager);
		$this->journalManager = new JournalReaderManager($this->commandExecutor);
	}

}

$test = new JournalReaderManagerTest();
$test->run();
