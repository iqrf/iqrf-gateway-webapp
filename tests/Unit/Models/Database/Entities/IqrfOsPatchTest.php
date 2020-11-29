<?php

/**
 * TEST: App\Models\Database\Entities\IqrfOsPatch
 * @covers App\Models\Database\Entities\IqrfOsPatch
 * @phpVersion >= 7.2
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\Models\Database\Entities;

use App\Models\Database\Entities\IqrfOsPatch;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for IqrfOsPatch database entity
 */
class IqrfOsPatchTest extends TestCase {

	/**
	 * @var IqrfOsPatch IqrfOsPatch entity
	 */
	private $iqrfOsPatch;

	/**
	 * IqrfOsPatch module type
	 */
	private const MODULE_TYPE = 'TR7x';

	/**
	 * IqrfOsPatch from DPA version
	 */
	private const FROM_DPA = 307;

	/**
	 * IqrfOsPatch from OS build
	 */
	private const FROM_OS = 2160;

	/**
	 * IqrfOsPatch to DPA version
	 */
	private const TO_DPA = 400;

	/**
	 * IqrfOsPatch to OS build
	 */
	private const TO_OS = 2225;

	/**
	 * IqrfOsPatch part number
	 */
	private const PART_NUMBER = 1;

	/**
	 * IqrfOsPatch parts
	 */
	private const PARTS = 1;

	/**
	 * IqrfOsPatch file name
	 */
	private const FILE_NAME = 'OS-TR7x-307(2160)-400(2225).iqrf';

	/**
	 * Sets up testing environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->iqrfOsPatch = new IqrfOsPatch(self::MODULE_TYPE, self::FROM_DPA, self::FROM_OS, self::TO_DPA, self::TO_OS, self::PART_NUMBER, self::PARTS, self::FILE_NAME);
	}

	/**
	 * Tests the function to return module type of patch
	 */
	public function testGetModuleType(): void {
		Assert::same(self::MODULE_TYPE, $this->iqrfOsPatch->getModuleType());
	}

	/**
	 * Tests the function to return patch from DPA version
	 */
	public function testGetFromVersion(): void {
		Assert::same(self::FROM_DPA, $this->iqrfOsPatch->getFromVersion());
	}

	/**
	 * Tests the function to return patch from OS build
	 */
	public function testGetFromBuild(): void {
		Assert::same(self::FROM_OS, $this->iqrfOsPatch->getFromBuild());
	}

	/**
	 * Tests the function to return patch target DPA version
	 */
	public function testGetToVersion(): void {
		Assert::same(self::TO_DPA, $this->iqrfOsPatch->getToVersion());
	}

	/**
	 * Tests the function to return patch target OS version
	 */
	public function testGetToBuild(): void {
		Assert::same(self::TO_OS, $this->iqrfOsPatch->getToBuild());
	}

	/**
	 * Tests the function to return patch part number
	 */
	public function testGetPart(): void {
		Assert::same(self::PART_NUMBER, $this->iqrfOsPatch->getPart());
	}

	/**
	 * Tests the function to return number of patch parts
	 */
	public function testGetParts(): void {
		Assert::same(self::PARTS, $this->iqrfOsPatch->getParts());
	}

	/**
	 * Tests the function to return patch file name
	 */
	public function testGetFileName(): void {
		Assert::same(self::FILE_NAME, $this->iqrfOsPatch->getFileName());
	}

	/**
	 * Tests the function to return JSON serialized patch metadata
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'id' => null,
			'moduleType' => self::MODULE_TYPE,
			'fromDpaVersion' => self::FROM_DPA,
			'fromOsBuild' => self::FROM_OS,
			'toDpaVersion' => self::TO_DPA,
			'toOsBuild' => self::TO_OS,
			'partNumber' => self::PART_NUMBER,
			'parts' => self::PARTS,
			'fileName' => self::FILE_NAME,
		];
		Assert::same($expected, $this->iqrfOsPatch->jsonSerialize());
	}

}

$test = new IqrfOsPatchTest();
$test->run();
