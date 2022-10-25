<?php

/**
 * TEST: App\GatewayModule\Models\JournalReaderManager
 * @covers App\GatewayModule\Models\JournalReaderManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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

use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\JsonFileManager;
use App\GatewayModule\Exceptions\JournalReaderArgumentException;
use App\GatewayModule\Exceptions\JournalReaderInternalException;
use App\GatewayModule\Models\JournalReaderManager;
use Nette\Utils\Json;
use Tester\Assert;
use Tests\Stubs\CoreModule\Models\Command;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Test for mini-journalreader manager
 */
final class JournalReaderManagerTest extends CommandTestCase {

	/**
	 * Path to directory with testing data
	 */
	private const DATA_DIR = TESTER_DIR . '/data/journal/';

	/**
	 * Commands
	 */
	private const COMMANDS = [
		'cursorless' => 'iqrf-journal-reader -j -n 10',
		'cursor' => 'iqrf-journal-reader -j -n 10 -e "s=c898cdeb1833489094a2e5f158e28858;i=39782bc;b=e38555478f2e49389c854801d0aa15c5;m=14855146;t=5ebca2f015ad3;x=bd0404fdde8c58c6"',
		'invalidCursor' => 'iqrf-journal-reader -j -n 10 -e "invalid"',
		'internalError' => 'iqrf-journal-reader -j -n 10 -e "s=c898cdeb1833489094a2e5f158e28858;i=39782bc;b=e38555478f2e49389c854801d0aa15c5;m=14855146;t=5ebca2f015ad3;x=bd0404fdde8c58c6"',
	];

	/**
	 * @var JsonFileManager $fileManager JSON file manager
	 */
	private JsonFileManager $fileManager;

	/**
	 * @var JournalReaderManager $journalManager Journal manager
	 */
	private JournalReaderManager $journalManager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(true, $commandStack);
		$this->fileManager = new JsonFileManager(self::DATA_DIR, $commandManager);
		$this->journalManager = new JournalReaderManager($this->commandManager);
	}

	/**
	 * Tests the function to get journal records without a cursor
	 */
	public function testGetRecordsCursorLess(): void {
		$raw = $this->fileManager->read('cursorless');
		$parsed = $this->fileManager->read('cursorless_parsed');
		$command = new Command(self::COMMANDS['cursorless'], Json::encode($raw), '', 0);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['cursorless'], true])
			->andReturn($command);
		Assert::equal($parsed, $this->journalManager->getRecords(10));
	}

	/**
	 * Tests the function to get journal records with a cursor
	 */
	public function testGetRecordsCursor(): void {
		$raw = $this->fileManager->read('cursor');
		$parsed = $this->fileManager->read('cursor_parsed');
		$command = new Command(self::COMMANDS['cursor'], Json::encode($raw), '', 0);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['cursor'], true])
			->andReturn($command);
		Assert::equal($parsed, $this->journalManager->getRecords(10, 's=c898cdeb1833489094a2e5f158e28858;i=39782bc;b=e38555478f2e49389c854801d0aa15c5;m=14855146;t=5ebca2f015ad3;x=bd0404fdde8c58c6'));
	}

	/**
	 * Tests the function to get journal records with invalid cursor
	 */
	public function testGetRecordsError(): void {
		$invalidCursorCommand = new Command(self::COMMANDS['invalidCursor'], '', 'Invalid cursor format: invalid', 1);
		$internalErrorCommand = new Command(self::COMMANDS['internalError'], '', 'Failed to get record cursor: Cannot assign requested address', 2);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['invalidCursor'], true])
			->andReturn($invalidCursorCommand);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['internalError'], true])
			->andReturn($internalErrorCommand);
		Assert::exception(function (): void {
			$this->journalManager->getRecords(10, 'invalid');
		}, JournalReaderArgumentException::class, 'Invalid cursor format: invalid');
		Assert::exception(function (): void {
			$this->journalManager->getRecords(10, 's=c898cdeb1833489094a2e5f158e28858;i=39782bc;b=e38555478f2e49389c854801d0aa15c5;m=14855146;t=5ebca2f015ad3;x=bd0404fdde8c58c6');
		}, JournalReaderInternalException::class, 'Failed to get record cursor: Cannot assign requested address');
	}

}

$test = new JournalReaderManagerTest();
$test->run();
