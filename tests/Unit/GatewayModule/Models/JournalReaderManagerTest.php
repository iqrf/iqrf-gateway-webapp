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
use App\GatewayModule\Exceptions\JournalReaderException;
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
		'cursorless' => 'mini-journalreader -j -n 10',
		'cursor' => 'mini-journalreader -j -n 10 -t "s=c898cdeb1833489094a2e5f158e28858;i=2098097;b=7c1ebb21607a46ed82039b58d5e4f4b3;m=48d069be;t=5e7fc84f8f984;x=e033f190643ea749"',
		'invalidCursor' => 'mini-journalreader -j -n 10 -t "error"',
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
		$raw = $this->fileManager->read('journal_10_1');
		$parsed = $this->fileManager->read('journal_10_1_parsed');
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
		$raw = $this->fileManager->read('journal_10_2');
		$parsed = $this->fileManager->read('journal_10_2_parsed');
		$command = new Command(self::COMMANDS['cursor'], Json::encode($raw), '', 0);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['cursor'], true])
			->andReturn($command);
		Assert::equal($parsed, $this->journalManager->getRecords(10, $parsed['endCursor']));
	}

	/**
	 * Tests the function to get journal records with invalid cursor
	 */
	public function testGetRecordsError(): void {
		$command = new Command(self::COMMANDS['invalidCursor'], '', 'Failed to seek to end/cursor: Invalid argument', 1);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['invalidCursor'], true])
			->andReturn($command);
		Assert::exception(function (): void {
			$this->journalManager->getRecords(10, 'error');
		}, JournalReaderException::class, 'Failed to seek to end/cursor: Invalid argument');
	}

}

$test = new JournalReaderManagerTest();
$test->run();
