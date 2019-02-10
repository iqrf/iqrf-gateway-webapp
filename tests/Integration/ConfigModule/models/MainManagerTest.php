<?php

/**
 * TEST: App\ConfigModule\Models\MainManager
 * @covers App\ConfigModule\Models\MainManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Test\Integration\ConfigModule\Models;

use App\ConfigModule\Models\MainManager;
use Tester\Assert;
use Tester\Environment;
use Tests\Toolkit\TestCases\JsonConfigTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for main configuration manager
 */
class MainManagerTest extends JsonConfigTestCase {

	/**
	 * @var string File name (without .json)
	 */
	private $fileName = 'config';

	/**
	 * Tests the function to load main configuration of daemon
	 */
	public function testLoad(): void {
		$manager = new MainManager($this->fileManager);
		$expected = $this->readFile($this->fileName);
		Assert::equal($expected, $manager->load());
	}

	/**
	 * Tests the function to save main configuration of daemon
	 */
	public function testSave(): void {
		Environment::lock('config_main', __DIR__ . '/../../../temp/');
		$manager = new MainManager($this->fileManagerTemp);
		$array = [
			'applicationName' => 'IqrfGatewayDaemon',
			'resourceDir' => '',
			'dataDir' => '/usr/share/iqrfgd2',
			'cacheDir' => '/var/cache/iqrfgd2',
			'userDir' => '',
			'configurationDir' => '/etc/iqrf-daemon',
			'deploymentDir' => '/usr/lib/iqrfgd2',
		];
		$expected = $this->readFile($this->fileName);
		$this->copyFile($this->fileName);
		$expected['configurationDir'] = '/etc/iqrf-daemon';
		$manager->save($array);
		Assert::equal($expected, $this->readTempFile($this->fileName));
	}

}

$test = new MainManagerTest();
$test->run();
